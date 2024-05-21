<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

use App\Http\Controllers\ReservableItemController;
use App\Models\ReservableItem;
use App\Models\Reservation;
use App\Models\ReservationGroup;
use App\Models\User;
use App\Mail\ReservationVerified;

class ReservationController extends Controller
{
    /**
     * Returns the reservation table for a given item.
     */
    public function index(ReservableItem $item) {
        $this->authorize('viewAny', ReservableItem::class);

        return response()->json($item->reservations);
    }

    /**
     * Returns the reservation table for the washing machines.
     */
    public function indexForWashingMachines() {
        $this->authorize('viewAny', ReservableItem::class);

        $items = ReservableItem::where("type", "washing_machine")->get();
        // for now:
        $from = Carbon::today()->startOfWeek();
        $until = $from->copy()->addDays(7);
        return view('reservations.washing_machines', [
            'items' => $items,
            'from' => $from,
            'until' => $until,
            'blocks' => $items->map(function ($item) use ($from, $until)
              {return ReservableItemController::listOfBlocks($item, $from, $until);
            })
        ]);
    }

    /**
     * Returns details about a reservation.
     */
    public function show(Reservation $reservation) {
        $this->authorize('view', $reservation);

        return view('reservations.show', ['reservation' => $reservation]);
    }

    /**
     * Returns a form for creating a reservation for a given item.
     */
    public function create(ReservableItem $item) {
        $this->authorize('requestReservation', $item);

        return view('reservations.edit', ['item' => $item]);
    }

    /**
     * Aborts the request with a 409 code
     * if the reservation given would conflict
     * with a previous one.
     */
    public static function abortIfConflicts(Reservation $reservation) {
        // there must not be any conflict with previous reservations
        $conflicts = $reservation->reservableItem
            ->reservationsInSlot(Carbon::make($reservation->reserved_from), Carbon::make($reservation->reserved_until))
            ->filter(function ($otherReservation) use ($reservation) {return $otherReservation->id != $reservation->id;});
        if (!$conflicts->isEmpty()) {
            // 409 Conflict
            abort(409, "reservation conflicts with a previous one: " .
            $conflicts->first()->reserved_from . " " .
            $conflicts->first()->reserved_until);
        }
    }

    /** The possible values of the 'for_what' field. */
    public const THIS_ONLY = 'this_only';
    public const ALL_AFTER = 'all_after';
    public const ALL = 'all';

    /**
     * The common validation function for store and update.
     * $isNew is true if we are calling this from store.
     */
    public static function validateReservationRequest(bool $isNew, Request $request) {
        // some of these will be hidden
        // depending on whether we are creating or editing a reservation
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'note'  => 'nullable|string|max:2047',
            'reserved_from' => 'required|date',
            'reserved_until' => 'required|date',
            'recurring' => 'in:on',
            'frequency' => [
                Rule::excludeIf(!$isNew || 'on' != $request->recurring),
                'required',
                'numeric',
                'min:1',
                'max:65535'
            ],
            // the first day cannot be set
            'last_day' => [
                Rule::excludeIf('on' != $request->recurring ||
                                    (!$isNew && self::THIS_ONLY == $request->for_what)),
                'required',
                'date'
            ],
            // this is only for editing
            'for_what' => [
                Rule::excludeIf($isNew || 'on' != $request->recurring),
                Rule::in([self::THIS_ONLY, self::ALL_AFTER, self::ALL])
            ]
        ]);
        $validator->after(function ($validator) use ($request) {
            if (isset($request->reserved_until) &&
                    Carbon::make($request->reserved_from) > Carbon::make($request->reserved_until)) {
                $validator->errors()->add(
                    'reserved_until', __('reservation.end_before_start')
                );
            } else if (isset($request->recurring) && isset($request->last_day)) {
                $thisDay = Carbon::make($request->reserved_from);
                $lastDay = Carbon::make($request->last_day);
                $thisDay->hour = $lastDay->hour;
                $thisDay->minute = $lastDay->minute;
                if ($thisDay > $lastDay) {
                    $validator->errors()->add(
                        'last_day', __('reservation.last_day_before_first_day')
                    );
                }
            }
        });
        return $validator->validate();
    }

    /**
     * Stores a reservation for a given item and user
     * with data given in the request.
     */
    public function store(ReservableItem $item, Request $request) {
        $this->authorize('requestReservation', $item);

        $validatedData = self::validateReservationRequest(true, $request);

        if (isset($validatedData['recurring'])) {
            if ('room' != $item->type) {
                abort(400, 'recurring reservations are only allowed for rooms');
            }
            // We have to create this outside of the transaction
            // in order to obtain an id and have all the other data
            // accessable.
            $newGroup = ReservationGroup::create([
                'default_item' => $item->id,
                'user_id' => user()->id,
                'frequency' => $validatedData['frequency'],
                'default_title' => $validatedData['title'],
                'default_from' => Carbon::make($validatedData['reserved_from']),
                'default_until' => Carbon::make($validatedData['reserved_until']),
                'default_note' => $validatedData['note'],
                'last_day' => Carbon::make($validatedData['last_day']),
                'verified' => Auth::user()->can('reserveImmediately', $item)
            ]);
            try {
                $newGroup->initializeFrom($validatedData['reserved_from']);
                return redirect(route('reservations.show', [
                    'reservation' => $newGroup->firstReservation()
                ]));
            } catch (ConflictException $e) {
                $newGroup->delete();
                abort(409, $e->getMessage());
            }

        } else { // if it is not recurring

            // this does _not_ save it yet!
            $newReservation = new Reservation();
            $newReservation->reservable_item_id = $item->id;
            $newReservation->user_id = user()->id;
            $newReservation->title = $validatedData['title'];
            $newReservation->note = $validatedData['note'];
            $newReservation->reserved_from
                = $validatedData['reserved_from'];
            $newReservation->reserved_until
                = $validatedData['reserved_until'];

            $newReservation->verified = Auth::user()->can('reserveImmediately', $item);

            // there must not be any conflict with previous reservations
            ReservationController::abortIfConflicts($newReservation);

            // and if everything is alright:
            $newReservation->save();
            return redirect(route('reservations.show', ['reservation' => $newReservation]));
        }
    }

    public function edit(Reservation $reservation) {
        $this->authorize('modify', $reservation);

        return view('reservations.edit', ['reservation' => $reservation]);
    }

    public function update(Reservation $reservation, Request $request) {
        $this->authorize('modify', $reservation);

        $validatedData =
            ReservationController::validateReservationRequest(false, $request);

        if (!is_null($reservation->group) && self::THIS_ONLY != $validatedData['for_what']) {

            // set the parameters we won't need to null
            // we don't have mechanisms for the default item, the frequency and the user yet
            $defaultItem = null; $user = null;
            $defaultTitle =
                $validatedData['title'] != $reservation->title
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

            // only downgrade if the intervals themselves change
            $verified =
                Auth::user()->can('reserveImmediately', $reservation->group->item)
                || ($reservation->group->verified &&
                    is_null($defaultFrom) && is_null($defaultUntil));

            try {
                if (self::ALL_AFTER == $validatedData['for_what']) {
                    $reservation->group->setForAllAfter(
                        reservation: $reservation,
                        defaultItem: $defaultItem,
                        user: $user,
                        // ?int $frequency = null, // it cannot be set for now
                        defaultTitle: $defaultTitle,
                        defaultFrom: $defaultFrom,
                        defaultUntil: $defaultUntil,
                        defaultNote: $defaultNote,
                        verified: $verified
                    );
                } else { // self::ALL == $validated['for_what']
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

                // and for both cases:
                if ($validatedData['last_day'] != $reservation->group->last_day) {
                    $reservation->group->setLastDay($validatedData['last_day']);
                }

                return redirect(route('reservations.show', ['reservation' => $reservation]));
            } catch (ConflictException $e) {
                abort(409, $e->getMessage());
            }
        } else { // if only for this, or it has no group whatsoever
            // this does _not_ save it yet!
            $reservation->title = $validatedData['title'];
            $reservation->note = $validatedData['note'];
            $reservation->reserved_from
                = $validatedData['reserved_from'];
            $reservation->reserved_until
                = $validatedData['reserved_until'];

            $item = $reservation->reservableItem;

            $reservation->verified = Auth::user()->can('reserveImmediately', $item);

            ReservationController::abortIfConflicts($reservation);

            // but it remains part of the group if it has one

            $reservation->save();
            return redirect(route('reservations.show', ['reservation' => $reservation]));
        }
    }

    /**
     * Lets an authorized user verify an unverified reservation.
     * Also sends an email to the owner.
     */
    public function verify(Reservation $reservation) {
        $this->authorize('administer', Reservation::class);

        $reservation->verified = true;
        $reservation->save();

        Mail::to($reservation->user)->queue(new ReservationVerified(
            $reservation->user->name,
            user()->name,
            $reservation
        ));

        return redirect(route('reservations.show', ['reservation' => $reservation]));
    }

    /**
     * Lets an authorized user verify the whole group of the reservation.
     * Also sends an email to the owner.
     */
    public function verifyAll(Reservation $reservation) {
        $this->authorize('administer', Reservation::class);
        if (!$reservation->isRecurring()) return $this->verify($reservation);

        $reservation->group->verify();

        // TODO: a different email for the whole group?
        Mail::to($reservation->user)->queue(new ReservationVerified(
            $reservation->user->name,
            user()->name,
            $reservation
        ));

        return redirect(route('reservations.show', ['reservation' => $reservation]));
    }

    public function delete(Reservation $reservation) {
        $this->authorize('modify', $reservation);
        $reservation->delete();
        return redirect(route('reservations.items.show', ['item' => $reservation->reservableItem]));
    }

    /**
     * Lets an authorized user delete the whole group of the reservation.
     */
    public function deleteAll(Reservation $reservation) {
        $this->authorize('modify', $reservation);
        if (!$reservation->isRecurring()) return $this->delete($reservation);

        $reservation->group->delete(); // this cascades
        return redirect(route('reservations.items.show', ['item' => $reservation->reservableItem]));
    }
}
