<?php

namespace App\Http\Controllers\Dormitory\Reservations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

use App\Enums\ReservableItemType;
use App\Models\User;
use App\Models\Role;
use App\Models\Reservations\ReservableItem;
use App\Models\Reservations\ReservationGroup;
use App\Models\Reservations\Reservation;
use App\Exceptions\ReservationConflictException;
use App\Mail\Reservations\ReservationDeleted;
use App\Mail\Reservations\ReservationVerified;

class ReservationController extends \App\Http\Controllers\Controller
{
    /**
     * Lists the details of a reservation.
     */
    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        return view('dormitory.reservations.show', [
            'reservation' => $reservation
        ]);
    }

    /**
     * Checks whether the maximum number of reservations for washing machines has been reached.
     */
    private static function reachedMaximumForWashingMachines(User $user): bool
    {
        return $user->reservations()
            ->where('reserved_until', '>', Carbon::now())
            ->whereHas('reservableItem', function ($query) {
                $query->where('type', ReservableItemType::WASHING_MACHINE)
                    ->where('out_of_order', false);
            })
            ->count() >= ReservableItem::MAX_WASHING_RESERVATIONS;
    }

    /**
     * Returns a form for creating a reservation.
     */
    public function create(Request $request, ReservableItem $item)
    {
        $this->authorize('requestReservation', $item);

        if ($item->isWashingMachine()
            && self::reachedMaximumForWashingMachines(user())) {
            return redirect()->back()->with('error', __('reservations.max_washing_reservations_reached'));
        } else {
            return view('dormitory.reservations.edit', [
                'item' => $item,
                // default values that might have been passed in the GET request
                'group_from' => $request->from,
                'group_until' => $request->until
            ]);
        }
    }

    /**
     * Returns a conflicting reservation if there is already one
     * which would conflict with the one given.
     * Note: we assume that the given reservation is not yet saved.
     * Returns null if there is no conflict.
     */
    private static function hasConflict(Reservation $newReservation): ?Reservation
    {
        $conflictingReservations = $newReservation->reservableItem->reservationsInSlot(
            CarbonImmutable::make($newReservation->reserved_from),
            CarbonImmutable::make($newReservation->reserved_until)
        );

        return ($conflictingReservations
                    ->filter(fn ($reservation) => $reservation->id != $newReservation->id)
                    ->first());
    }

    /**
     * Notifies the secretary and staff on a new or modified reservation that needs to be verified.
     * Does nothing if the user is a secretary or staff member themselves.
     */
    private static function notifyOnVerifiableReservation(Reservation $reservation)
    {
        if (!user()->hasRole([Role::SECRETARY, Role::STAFF])) {
            $thoseToNotify = User::whereHas('roles', function ($query) {
                $query->whereIn('name', [Role::SECRETARY, Role::STAFF]);
            })->get();
            foreach ($thoseToNotify as $toNotify) {
                Mail::to($toNotify)->queue(
                    new \App\Mail\Reservations\ReservationRequested(
                        $reservation,
                        $toNotify->name
                    )
                );
            }
        }
    }

    /**
     * An auxiliary function for `store`;
     * validates the request and returns the validated data.
     */
    private static function validateNewReservation(ReservableItem $item, Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2047',
            'reserved_from' => 'required|date',
            'reserved_until' => [
                Rule::excludeIf($item->isWashingMachine()),
                'required', 'date', 'after:reserved_from', 'after:now'
            ],
            'recurring' => 'in:on',
            'last_day' => 'exclude_unless:recurring,on|required|date',
            'frequency' => 'exclude_unless:recurring,on|required|numeric|min:1|max:65535'
        ]);

        $validator->sometimes(
            'last_day',
            'after_or_equal:' . Carbon::make($request->reserved_from)->setHour(0)->setMinute(0)->setSecond(0),
            fn ($input) => !$item->isWashingMachine() && isset($input->recurring)
        );

        $validator->sometimes(
            'reserved_from',
            'after_or_equal:' . Carbon::now()->setMinute(0)->setSecond(0),
            fn ($input) => $item->isWashingMachine()
        );

        if ($item->isWashingMachine()) {
            $validator->after(function ($validator) use ($request) {
                $from = Carbon::make($request->reserved_from);
                // we only allow one-hour slots for washing machines
                if (0 != $from->minute || 0 != $from->second) {
                    $validator->errors()->add('reserved_from', __('reservations.one_hour_slot_only'));
                }
            });
        }

        return $validator->validate();
    }

    /**
     * An auxiliary function for `store`.
     * Handles validated data which we know to belong to
     * a new recurring reservation.
     * Returns the first reservation of the new group.
     * If there has been a conflict,
     * it throws a ReservationConflictException
     * with the error message to show to the user
     * and does nothing to the database.
     */
    private static function handleNewRecurringReservation(ReservableItem $item, array $validatedData): Reservation
    {
        return DB::transaction(function () use ($item, $validatedData) {
            $newGroup = ReservationGroup::create([
                'group_item' => $item->id,
                'user_id' => user()->id,
                'frequency' => intval($validatedData['frequency']),
                'group_title' => $validatedData['title'],
                'group_from' => Carbon::make($validatedData['reserved_from']),
                'group_until' => Carbon::make($validatedData['reserved_until']),
                'group_note' => $validatedData['note'],
                'last_day' => Carbon::make($validatedData['last_day']),
                'verified' => user()->can('autoVerify', $item)
            ]);

            try {
                $newGroup->initializeFrom($validatedData['reserved_from']);
            } catch (ReservationConflictException $e) {
                $newGroup->delete();
                throw $e;
            }
            return $newGroup->firstReservation();
        });
    }

    /**
     * An auxiliary function for `store`.
     * Handles validated data which we know to belong to
     * a new non-recurring reservation;
     * then returns the new reservation.
     * If there has been a conflict,
     * it throws a ReservationConflictException
     * with the error message to show to the user
     * and does nothing to the database.
     */
    private static function handleNewSingleReservation(ReservableItem $item, array $validatedData): Reservation
    {
        // we do not save it yet!
        $newReservation = new Reservation();
        $newReservation->reservable_item_id = $item->id;
        $newReservation->user_id = user()->id;
        $newReservation->title = $validatedData['title'] ?? null;
        $newReservation->note = $validatedData['note'];
        $newReservation->reserved_from = $validatedData['reserved_from'];
        $newReservation->reserved_until =
            $item->isWashingMachine()
            ? Carbon::make($validatedData['reserved_from'])->addHour()
            : Carbon::make($validatedData['reserved_until']);


        $newReservation->verified = user()->can('autoVerify', $item);

        $conflictingReservation = self::hasConflict($newReservation);
        if ($conflictingReservation) {
            throw new ReservationConflictException(__('reservations.already_exists') . ' ' .
                "{$conflictingReservation->reserved_from},
                {$conflictingReservation->reserved_until}");
        }

        // and finally:
        $newReservation->save();
        return $newReservation;
    }

    /**
     * Stores a reservation based on
     * a ReservableItem provided separately
     * and the data in the request.
     */
    public function store(ReservableItem $item, Request $request)
    {
        $this->authorize('requestReservation', $item);

        if ($item->isWashingMachine()
            && self::reachedMaximumForWashingMachines(user())) {
            return redirect()->back()->with('error', __('reservations.max_washing_reservations_reached'));
        }

        $validatedData = self::validateNewReservation($item, $request);

        try {
            if ($item->isRoom() && isset($request->recurring)) {
                $reservation = self::handleNewRecurringReservation($item, $validatedData);
            } else {
                $reservation = self::handleNewSingleReservation($item, $validatedData);
            }
        } catch (ReservationConflictException $e) {
            return redirect()->back()->withInput($validatedData)->with('error', $e->getMessage());
        }

        if (!$reservation->verified) {
            self::notifyOnVerifiableReservation($reservation);
        }

        return redirect()->route(
            'reservations.show',
            $reservation
        )->with(
            'message',
            $reservation->verified ? __('general.successful_modification') : __('reservations.verifiers_notified')
        );
    }

    /**
     * Returns a form for creating a reservation.
     */
    public function edit(Reservation $reservation)
    {
        // this also makes some other checks
        $this->authorize('modify', $reservation);

        return view('dormitory.reservations.edit', [
            'reservation' => $reservation
        ]);
    }

    public const EDIT_THIS_ONLY = 'edit_this_only';
    public const EDIT_ALL_AFTER = 'edit_all_after';
    public const EDIT_ALL = 'edit_all';

    /**
     * An auxiliary function for `update`;
     * validates the request and returns the validated data
     * in an array.
     */
    private static function validateModifiedReservation(Reservation $reservation, Request $request): array
    {
        $item = $reservation->reservableItem;

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2047',
            'reserved_from' => 'required|date',
            'reserved_until' => [
                Rule::excludeIf($item->isWashingMachine()),
                'required', 'date', 'after:reserved_from', 'after:now'
            ],
            'for_what' => [
                Rule::excludeIf(!$reservation->isRecurring()),
                'required',
                Rule::in([self::EDIT_THIS_ONLY, self::EDIT_ALL_AFTER, self::EDIT_ALL])
            ],
            'last_day' => [
                Rule::excludeIf(!$reservation->isRecurring()
                                || self::EDIT_THIS_ONLY == $request->for_what),
                'required', 'date'
            ]
        ]);

        $validator->sometimes(
            'last_day',
            'after_or_equal:' . Carbon::make($request->reserved_from)->setHour(0)->setMinute(0)->setSecond(0),
            fn ($input) => !$item->isWashingMachine() && $reservation->isRecurring() && self::EDIT_THIS_ONLY != $input->for_what
        );

        $validator->sometimes(
            'reserved_from',
            'after_or_equal:' . Carbon::now()->setMinute(0)->setSecond(0),
            fn ($input) => $item->isWashingMachine()
        );

        return $validator->validate();
    }

    /**
     * An auxiliary function for `update`.
     * Handles a request which we know to modify a group
     * (with option EDIT_ALL or EDIT_ALL_AFTER),
     * but does not yet reply to the request.
     * Throws a ReservationConflictException
     * with the error message to show to the user
     * if there has been a conflict.
     */
    private static function handleModifiedGroup(Reservation $reservation, array $validatedData): void
    {
        $group = $reservation->group;

        // the last day has to be modified first
        // because otherwise, the new reservations might not get the new dates etc.
        if ($validatedData['last_day'] != $reservation->group->last_day) {
            $group->setLastDay($validatedData['last_day']);
        }
        $group->refresh();

        $groupTitle =
            ($validatedData['title'] != $group->group_title)
            ? $validatedData['title'] : null;

        $groupFrom = Carbon::make($validatedData['reserved_from']);
        $groupUntil = Carbon::make($validatedData['reserved_until']);
        if (Carbon::make($validatedData['reserved_from']) == Carbon::make($reservation->reserved_from)
                && Carbon::make($validatedData['reserved_until']) == Carbon::make($reservation->reserved_until)) {
            $groupFrom = null;
            $groupUntil = null;
        }

        $groupNote =
            $validatedData['note'] != $reservation->note
            ? $validatedData['note'] : null;

        // for now:
        $groupItem = null;
        $user = null;

        $verified = user()->can('autoVerify', $reservation->reservableItem)
            || $group->verified && is_null($groupFrom) && is_null($groupUntil);

        // this is the part which can throw
        if (self::EDIT_ALL_AFTER == $validatedData['for_what']) {
            $group->setForAllAfter(
                firstReservation: $reservation,
                groupItem: $groupItem,
                user: $user,
                // ?int $frequency = null, // it cannot be set for now
                groupTitle: $groupTitle,
                groupFrom: $groupFrom,
                groupUntil: $groupUntil,
                groupNote: $groupNote,
                verified: $verified
            );
        } else { // self::EDIT_ALL== $validatedData['for_what']
            $group->setForAll(
                groupItem: $groupItem,
                user: $user,
                // ?int $frequency = null, // it cannot be set for now
                groupTitle: $groupTitle,
                groupFrom: $groupFrom,
                groupUntil: $groupUntil,
                groupNote: $groupNote,
                verified: $verified
            );
        }
    }

    /**
     * An auxiliary function for `update`.
     * Handles a request which we know to modify only one reservation
     * (either a standalone one or a  member of a group with option EDIT_THIS_ONLY),
     * but does not yet reply to the request.
     * Throws a ReservationConflictException
     * with the error message to show to the user
     * if there has been a conflict.
     */
    private static function handleModifiedSingleReservation(Reservation $reservation, array $validatedData): void
    {
        // we do not save it yet!

        $reservation->verified = user()->can('autoVerify', $reservation->reservableItem)
            || ($reservation->verified
                && $reservation->reserved_from == "{$validatedData['reserved_from']}"
                && $reservation->reserved_until == "{$validatedData['reserved_until']}");

        $reservation->title = $validatedData['title'] ?? null;
        $reservation->note = $validatedData['note'];
        $reservation->reserved_from = $validatedData['reserved_from'];
        $reservation->reserved_until =
            $reservation->reservableItem->isWashingMachine()
            ? Carbon::make($validatedData['reserved_from'])->addHour()
            : Carbon::make($validatedData['reserved_until']);

        $conflictingReservation = self::hasConflict($reservation);
        if ($conflictingReservation) {
            throw new ReservationConflictException(
                __('reservations.already_exists') . ' ' .
                "{$conflictingReservation->reserved_from},
                    {$conflictingReservation->reserved_until}"
            );
        }
        // and finally:
        $reservation->save();
    }

    /**
     * Updates a reservation with an edited version.
     */
    public function update(Reservation $reservation, Request $request)
    {
        // this also makes some other checks
        $this->authorize('modify', $reservation);

        $validatedData = self::validateModifiedReservation($reservation, $request);

        try {
            if ($reservation->isRecurring()
                    && self::EDIT_THIS_ONLY != $validatedData['for_what']) {
                self::handleModifiedGroup($reservation, $validatedData);
            } else {
                self::handleModifiedSingleReservation($reservation, $validatedData);
            }
        } catch (ReservationConflictException $e) {
            return redirect()->back()->withInput($validatedData)->with('error', $e->getMessage());
        }

        $reservation->refresh();
        if (!$reservation->verified) {
            self::notifyOnVerifiableReservation($reservation);
        }

        return redirect()->route(
            'reservations.show',
            $reservation
        )->with(
            'message',
            $reservation->verified ? __('general.successful_modification') : __('reservations.verifiers_notified')
        );
    }

    /**
     * Enables a user with administrative rights to approve a reservation.
     */
    public function verify(Reservation $reservation)
    {
        $this->authorize('administer', Reservation::class);
        if ($reservation->verified) {
            return redirect()->back()->with('error', __('reservations.already_verified'));
        } else {
            $reservation->verified = true;
            $reservation->save();

            Mail::to($reservation->user)->queue(new ReservationVerified(
                user()->name,
                $reservation
            ));

            return redirect()->route('reservations.show', $reservation);
        }
    }

    /**
     * Enables a user with administrative rights to approve
     * all the reservations of a group.
     */
    public function verifyAll(Reservation $reservation)
    {
        $this->authorize('administer', Reservation::class);

        if ($reservation->verified) {
            return redirect()->back()->with('error', __('reservations.already_verified'));
        } elseif (!$reservation->isRecurring()) {
            return redirect()->back()->with('error', __('reservations.bad_for_non-recurring'));
        } else {
            $reservation->group->setForAll(
                verified: true
            );

            if (user()->isNot($reservation->user)) {
                Mail::to($reservation->user)->queue(new ReservationVerified(
                    user()->name,
                    $reservation
                ));
            }

            return redirect()->route('reservations.show', $reservation);
        }
    }

    /**
     * Deletes a reservation.
     */
    public function delete(Reservation $reservation)
    {
        $this->authorize('modify', $reservation);

        // we will need these for the mailable
        $ownerName = $reservation->user->name;
        $itemName = $reservation->reservableItem->name;
        $reservationArray = $reservation->toArray();

        $reservation->delete();

        if ($reservation->user->id != user()->id) {
            Mail::to($reservation->user)->queue(new ReservationDeleted(
                user()->name,
                $ownerName,
                $itemName,
                $reservationArray
            ));
        }

        if ($reservation->reservableItem->isWashingMachine()) {
            return redirect()->route('reservations.items.index', ['type' => ReservableItemType::WASHING_MACHINE]);
        } else {
            return redirect()->route('reservations.items.show', $reservation->reservableItem);
        }
    }

    /**
     * Deletes a reservation.
     */
    public function deleteAll(Reservation $reservation)
    {
        $this->authorize('modify', $reservation);

        if (!$reservation->isRecurring()) {
            return redirect()->back()->with('error', __('reservations.not_a_recurring_reservation'));
        }

        // we will need these for the mailable
        $ownerName = $reservation->user->name;
        $itemName = $reservation->reservableItem->name;
        $reservationArray = $reservation->toArray();

        DB::transaction(function () use ($reservation) {
            $group = $reservation->group;
            $group->reservations()->delete();
            $group->delete();
        });

        if ($reservation->user->id != user()->id) {
            Mail::to($reservation->user)->queue(new ReservationDeleted(
                user()->name,
                $ownerName,
                $itemName,
                $reservationArray,
                isForAll: true
            ));
        }

        return redirect()->route('reservations.items.show', $reservation->reservableItem);
    }
}
