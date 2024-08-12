<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\Query\Builder;

use Carbon\Carbon;

use App\Models\User;
use App\Models\ReservableItem;
use App\Models\ReservationGroup;
use App\Models\Reservation;
use App\Models\ConflictException;
use App\Mail\ReservationVerified;

class ReservationController extends Controller
{
    /**
     * Lists reservations for a given item.
     */
    public function index(ReservableItem $item)
    {
        $this->authorize('viewAny', ReservableItem::class);

        return response()->json(
            Reservation::where('reservable_item_id', $item->id)
                         ->orderBy('reserved_from')
                         ->get()
        );
    }

    /**
     * Lists reservations for all washing machines.
     */
    public function indexForWashingMachines()
    {
        $this->authorize('viewAny', ReservableItem::class);

        $items = ReservableItem::where('type', 'washing_machine')->get();
        $from = Carbon::today()->startOfWeek();
        $until = $from->copy()->addDays(7);
        return view('reservations.index_for_washing_machines', [
            'items' => $items->all(),
            'from' => $from,
            'until' => $until
        ]);
    }

    /**
     * Lists the details of a reservation.
     */
    public function show(Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        return view('reservations.show', [
            'reservation' => $reservation
        ]);
    }

    /**
     * Checks whether the maximum number of reservations for washing machines has been reached.
     */
    private static function reachedMaximumForWashingMachines(User $user): bool
    {
        return ReservableItem::MAX_WASHING_RESERVATIONS
          <= Reservation::where('user_id', $user->id)
                ->where('reserved_until', '>', Carbon::now())
                ->whereExists(function (Builder $query) {
                    $query->select(DB::raw(1))
                          ->from('reservable_items')
                          ->where('type', ReservableItem::WASHING_MACHINE)
                          ->where('out_of_order', false)
                          ->whereColumn('reservable_items.id', 'reservations.reservable_item_id');
                })->count();
    }

    /**
     * Returns a form for creating a reservation.
     */
    public function create(Request $request, ReservableItem $item)
    {
        $this->authorize('requestReservation', $item);

        if ($item->isOutOfOrder()) {
            return redirect()->back()->with('error', __('reservations.item_out_of_order'));
        } elseif ($item->isWashingMachine()
            && self::reachedMaximumForWashingMachines(user())) {
            return redirect()->back()->with('error', __('reservations.max_washing_reservations_reached'));
        } else {
            return view('reservations.edit', [
                'item' => $item,
                // default values that might have been passed in the GET request
                'default_from' => $request->from,
                'default_until' => $request->until
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
        $conflictingReservations = ReservableItem::find($newReservation->reservable_item_id)
            ->reservationsInSlot($newReservation->reserved_from, $newReservation->reserved_until);

        return ($conflictingReservations
                    ->filter(fn($reservation) => $reservation->id != $newReservation->id)
                    ->first());
    }

    /**
     * Stores a reservation based on
     * a ReservableItem provided separately
     * and the data in the request.
     */
    public function store(ReservableItem $item, Request $request)
    {
        $this->authorize('requestReservation', $item);

        if ($item->isOutOfOrder()) {
            return redirect()->back()->with('error', __('reservations.item_out_of_order'));
        } elseif ($item->isWashingMachine()
            && self::reachedMaximumForWashingMachines(user())) {
            return redirect()->back()->with('error', __('reservations.max_washing_reservations_reached'));
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2047',
            'reserved_from' => 'required|date',
            'reserved_until' => [
                Rule::excludeIf($item->isWashingMachine()),
                'required', 'date'
            ],
            'recurring' => 'in:on',
            'last_day' => 'exclude_unless:recurring,on|required|date',
            'frequency' => 'exclude_unless:recurring,on|required|numeric|min:1|max:65535'
        ]);

        if (!$item->isWashingMachine()) $validator->after(function ($validator) use ($request, $item) {
            if ($request->reserved_from > $request->reserved_until) {
                $validator->errors()->add('reserved_until',
                    __('reservations.end_before_start'));
            }
            if (Carbon::make($request->reserved_until) < Carbon::now()) {
                $validator->errors()->add('reserved_until', __('reservations.in_past'));
            }
            if (isset($request->recurring)
                       && Carbon::make($request->reserved_from)
                         > Carbon::make($request->last_day)->addDay()) {
                $validator->errors()->add('last_day',
                    __('reservations.last_day_before_first'));
            }
        }); else $validator->after(function ($validator) use ($request, $item) {
            $from = Carbon::make($request->reserved_from);
            // we only allow one-hour slots for washing machines
            if (0 != $from->minute || 0 != $from->second) {
                $validator->errors()->add('reserved_from', __('reservations.one_hour_slot_only'));
            }
            if (Carbon::make($request->reserved_from)->addHour() < Carbon::now()) {
                $validator->errors()->add('reserved_from', __('reservations.in_past'));
            }
        });

        $validatedData = $validator->validate();

        if (isset($validatedData['recurring']) && $item->isWashingMachine()) {
            return redirect()->back()
                ->with('error', __('reservations.no_recurring_for_washing_machines'));
        }

        if (isset($request->recurring)) {
            $newGroup = ReservationGroup::create([
                'default_item' => $item->id,
                'user_id' => user()->id,
                'frequency' => intval($validatedData['frequency']),
                'default_title' => $validatedData['title'],
                'default_from' => Carbon::make($validatedData['reserved_from']),
                'default_until' => Carbon::make($validatedData['reserved_until']),
                'default_note' => $validatedData['note'],
                'last_day' => Carbon::make($validatedData['last_day']),
                'verified' => false
            ]);

            try {
                $newGroup->initializeFrom($request->reserved_from);
            } catch (ConflictException $e) {
                $newGroup->delete();
                return redirect()->back()->with('error', __('reservations.recurring_conflict') . ": {$e->getMessage()}");
            }

            return redirect()->route('reservations.show',
                $newGroup->firstReservation()
            );
        } else {
            // we do not save it yet!
            $newReservation = new Reservation();
            $newReservation->reservable_item_id = $item->id;
            $newReservation->user_id = user()->id;
            $newReservation->title = $validatedData['title'] ?? null;
            $newReservation->note = $validatedData['note'];
            $newReservation->reserved_from = Carbon::make($validatedData['reserved_from']);
            $newReservation->reserved_until = 
                $item->isWashingMachine()
                ? $newReservation->reserved_from->copy()->addHour()
                : Carbon::make($validatedData['reserved_until']);

            $newReservation->verified = $item->isWashingMachine();

            $conflictingReservation = self::hasConflict($newReservation);
            if ($conflictingReservation) {
                return redirect()->back()->with('error',
                    __('reservations.already_exists') .
                    "{$conflictingReservation->reserved_from},
                    {$conflictingReservation->reserved_until}"
                );
            }

            // and finally:
            $newReservation->save();
            if ($item->isWashingMachine()) {
                return redirect()->route('reservations.index_for_washing_machines');
            } else {
                return redirect()->route('reservations.items.show', $item);
            }
        }
    }

    /**
     * Returns a form for creating a reservation.
     */
    public function edit(Reservation $reservation)
    {
        $this->authorize('modify', $reservation);

        if ($reservation->reservableItem->isOutOfOrder()) {
            return redirect()->back()->with('error', __('reservations.item_out_of_order'));
        } elseif (Carbon::make($reservation->reserved_until) < Carbon::now()) {
            return redirect()->back()->with('error', __('reservations.editing_past_reservations'));
        }

        return view('reservations.edit', [
            'reservation' => $reservation
        ]);
    }

    public const THIS_ONLY = 'this_only';
    public const ALL_AFTER = 'all_after';
    public const ALL = 'all';

    /**
     * Updates a reservation with an edited version.
     */
    public function update(Reservation $reservation, Request $request)
    {
        $this->authorize('modify', $reservation);

        if ($reservation->reservableItem->isOutOfOrder()) {
            return redirect()->back()->with('error', __('reservations.item_out_of_order'));
        } elseif (Carbon::make($reservation->reserved_until) < Carbon::now()) {
            return redirect()->back()->with('error', __('reservations.editing_past_reservations'));
        }

        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2047',
            'reserved_from' => 'required|date',
            'reserved_until' => [
                Rule::excludeIf($reservation->reservableItem->isWashingMachine()),
                'required', 'date'
            ],
            'for_what' => [
                Rule::excludeIf(!$reservation->isRecurring()),
                'required',
                Rule::in([self::THIS_ONLY, self::ALL_AFTER, self::ALL])
            ],
            'last_day' => [
                Rule::excludeIf(!$reservation->isRecurring()
                                || self::THIS_ONLY == $request->for_what),
                'required', 'date'
            ]
        ]);

        if (!$reservation->reservableItem->isWashingMachine()) $validator->after(function ($validator) use ($request, $reservation) {
            if ($request->reserved_from > $request->reserved_until) {
                $validator->errors()->add('reserved_until',
                    __('reservations.end_before_start'));
            }
            if ($reservation->isRecurring() && self::THIS_ONLY != $request->for_what
                       && Carbon::make($request->reserved_from)
                         > Carbon::make($request->last_day)->addDay()) {
                $validator->errors()->add('last_day',
                    __('reservations.last_day_before_first'));
            }
        });

        $validatedData = $validator->validate();

        if (!$reservation->isRecurring()
            || self::THIS_ONLY == $validatedData['for_what']) {
            // we do not save it yet!
            $reservation->title = $validatedData['title'] ?? null;
            $reservation->note = $validatedData['note'];
            $reservation->reserved_from = Carbon::make($validatedData['reserved_from']);
            $reservation->reserved_until = 
                $reservation->reservableItem->isWashingMachine()
                ? $reservation->reserved_from->copy()->addHour()
                : Carbon::make($validatedData['reserved_until']);

            $reservation->verified = $reservation->reservableItem->isWashingMachine();

            $conflictingReservation = self::hasConflict($reservation);
            if ($conflictingReservation) {
                return redirect()->back()->with('error',
                    __('reservations.already_exists') .
                    "{$conflictingReservation->reserved_from},
                     {$conflictingReservation->reserved_until}"
                );
            }

            // and finally:
            $reservation->save();
        } else {
            try {
                $defaultTitle =
                ($validatedData['title'] != $reservation->title)
                ? $validatedData['title'] : null;
                $defaultFrom =
                Carbon::make($validatedData['reserved_from']) != Carbon::make($reservation->reserved_from)
                ? Carbon::make($validatedData['reserved_from']) : null;
                $defaultUntil =
                    Carbon::make($validatedData['reserved_until']) != Carbon::make($reservation->reserved_until)
                    ? Carbon::make($validatedData['reserved_until']) : null;
                $defaultNote =
                    $validatedData['note'] != $reservation->note
                    ? $validatedData['note'] : null;

                // for now:
                $defaultItem = null; $user = null;

                $verified = $reservation->group->verified
                        && is_null($defaultFrom) && is_null($defaultUntil);

                if (self::ALL_AFTER == $validatedData['for_what']) {
                    $reservation->group->setForAllAfter(
                        firstReservation: $reservation,
                        defaultItem: $defaultItem,
                        user: $user,
                        // ?int $frequency = null, // it cannot be set for now
                        defaultTitle: $defaultTitle,
                        defaultFrom: $defaultFrom,
                        defaultUntil: $defaultUntil,
                        defaultNote: $defaultNote,
                        verified: $verified
                    );
                } else { // self::ALL == $validatedData['for_what']
                    $reservation->group->setForAll(
                        defaultItem: $defaultItem,
                        user: $user,
                        // ?int $frequency = null, // it cannot be set for now
                        defaultTitle: $defaultTitle,
                        defaultFrom: $defaultFrom,
                        defaultUntil: $defaultUntil,
                        defaultNote: $defaultNote,
                        verified: $verified
                    );
                }

                // and for the last day:
                if ($validatedData['last_day'] != $reservation->group->last_day) {
                    $reservation->group->setLastDay($validatedData['last_day']);
                }
            } catch (ConflictException $e) {
                abort(409, $e->getMessage());
            }
        }

        return redirect()->route('reservations.show', $reservation);
    }

    /**
     * Enables a user with administrative rights to approve a reservation.
     */
    public function verify(Reservation $reservation) {
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
    public function verifyAll(Reservation $reservation) {
        $this->authorize('administer', Reservation::class);

        if ($reservation->verified) {
            return redirect()->back()->with('error', __('reservations.already_verified'));
        } else if (!$reservation->isRecurring()) {
            return redirect()->back()->with('error', __('reservations.bad_for_non-recurring'));
        } else {
            $reservation->group->setForAll(
                verified: true
            );

            if (user()->isNot($reservation->user))
            Mail::to($reservation->user)->queue(new ReservationVerified(
                user()->name,
                $reservation
            ));

            return redirect()->route('reservations.show', $reservation);
        }
    }

    /**
     * Deletes a reservation.
     */
    public function delete(Reservation $reservation) {
        $this->authorize('modify', $reservation);

        $reservation->delete();
        if ($reservation->reservableItem->isWashingMachine()) {
            return redirect()->route('reservations.index_for_washing_machines');
        } else {
            return redirect()->route('reservations.items.show', $reservation->reservableItem);
        }
    }

    /**
     * Deletes a reservation.
     */
    public function deleteAll(Reservation $reservation) {
        $this->authorize('modify', $reservation);

        if (!$reservation->isRecurring()) {
            abort(400); // TODO: check this out
        }

        $reservation->group->delete();
        return redirect()->route('reservations.items.show', $reservation->reservableItem);
    }
}
