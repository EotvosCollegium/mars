<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ReservableItemController;
use App\Models\ReservableItem;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;

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
     * Validates requests intended to create or update a reservation.
     */
    public static function validateReservationRequest(Request $request): array {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'note'  => 'nullable|string|max:2047',
            'reserved_from_date' => 'required|date_format:Y-m-d',
            'reserved_from_time' => 'required|date_format:H:i',
            'reserved_until_date' => 'required|date_format:Y-m-d',
            'reserved_until_time' => 'required|date_format:H:i',
        ]);
        // does not seem to do anything
        $validator->after(function ($validator) {
            $reserved_from = Carbon::make(
                $validator->safe()->reserved_from_date
                . ' ' . $validator->safe()->reserved_from_time
            );
            $reserved_until = Carbon::make(
                $validator->safe()->reserved_until_date
                . ' ' . $validator->safe()->reserved_until_time
            );

            if ($reserved_from > $reserved_until) {
                $validator->errors()->add(
                    // TODO: it does not find the correct field yet
                    'title', 'A reservation cannot end before its start.'
                );
            }
        });
        return $validator->validate();
    }

    /**
     * Aborts the request with a 409 code
     * if the reservation given would conflict
     * with a previous one.
     */
    public static function abortIfConflicts(Reservation $reservation) {
        // there must not be any conflict with previous reservations
        $conflicts = $reservation->reservableItem
            ->reservationsInSlot(Carbon::make($reservation->reserved_from), Carbon::make($reservation->reserved_until));
        if (!$conflicts->isEmpty()) {
            // 409 Conflict
            abort(409, "reservation conflicts with a previous one: " .
            $conflicts->first()->reserved_from . " " .
            $conflicts->first()->reserved_until);
        }
    }

    /**
     * Stores a reservation for a given item and user
     * with data given in the request.
     */
    public function store(ReservableItem $item, Request $request) {
        $this->authorize('requestReservation', $item);

        $validatedData =
            ReservationController::validateReservationRequest($request);

        // this does _not_ save it yet!
        $newReservation = new Reservation();
        $newReservation->reservable_item_id = $item->id;
        $newReservation->user_id = user()->id;
        $newReservation->title = $validatedData['title'];
        $newReservation->note = $validatedData['note'];
        $newReservation->reserved_from
            = $validatedData['reserved_from_date'] . ' ' . $validatedData['reserved_from_time'];
        $newReservation->reserved_until
            = $validatedData['reserved_until_date'] . ' ' . $validatedData['reserved_until_time'];;

        $newReservation->verified = Auth::user()->can('reserveImmediately', $item);

        // there must not be any conflict with previous reservations
        ReservationController::abortIfConflicts($newReservation);

        // and if everything is alright:
        $newReservation->save();
        return redirect(route('reservations.show', ['reservation' => $newReservation]));
    }

    public function edit(Reservation $reservation) {
        $this->authorize('modify', $reservation);

        return view('reservations.edit', ['reservation' => $reservation]);
    }

    public function update(Reservation $reservation, Request $request) {
        $this->authorize('modify', $reservation);

        $validatedData =
            ReservationController::validateReservationRequest($request);

        // this does _not_ save it yet!
        $reservation->title = $validatedData['title'];
        $reservation->note = $validatedData['note'];
        $reservation->reserved_from
            = $validatedData['reserved_from_date'] . ' ' . $validatedData['reserved_from_time'];
        $reservation->reserved_until
            = $validatedData['reserved_until_date'] . ' ' . $validatedData['reserved_until_time'];

        $item = $reservation->reservableItem;

        $reservation->verified = Auth::user()->can('reserveImmediately', $item);

        ReservationController::abortIfConflicts($reservation);

        $reservation->save();
        return redirect(route('reservations.show', ['reservation' => $reservation]));
    }

    public function delete(Reservation $reservation) {
        $this->authorize('modify', $reservation);

        $reservation->delete();
        return redirect(route('reservations.items.show', ['item' => $reservation->reservableItem]));
    }
}
