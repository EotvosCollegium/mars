<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\ReservableItem;
use App\Models\Reservation;
use App\Models\User;

class ReservationController extends Controller
{
    /**
     * Returns the reservation table for a given item.
     */
    public function index(ReservableItem $item) {
        return response()->json($item->reservations);
    }

    /**
     * Returns the reservation table for the washing machines.
     */
    public function indexForWashingMachines() {
        return response()->json(
            ReservableItem::where("type", "washing_machine")->get()
                      ->map(function (ReservableItem $machine) {return $machine->reservations;})
        );
    }

    /**
     * Returns details about a reservation.
     */
    public function show(Reservation $reservation) {
        return response()->json($reservation);
    }

    /**
     * Returns a form for creating a reservation for a given item.
     */
    public function create(ReservableItem $item) {
        return response()->json(["szép form"]);
    }

    /**
     * Stores a reservation for a given item and user
     * with data given in the request.
     */
    public function store(ReservableItem $item, Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'note'  => 'required|string|max:2047',
            'reserved_from' => 'required|date',
            'reserved_until' => 'required|date'
        ]);
        /*
        // does not seem to do anything
        $validator->after(function ($validator) use ($request) {
            if (strtotime($request->reserved_from) > strtotime($request->reserved_until)) {
                $validator->errors()->add(
                    'reserved_until', 'A reservation cannot end before its start.'
                );
            }
        });
        */
        $validatedData = $validator->validate();

        // this does _not_ save it yet!
        $newReservation = new Reservation();
        $newReservation->reservable_item_id = $item->id;
        $newReservation->user_id = user()->id;
        $newReservation->verified = true; // for now at least
        $newReservation->title = $validatedData['title'];
        $newReservation->note = $validatedData['note'];
        $newReservation->reserved_from = $validatedData['reserved_from'];
        $newReservation->reserved_until = $validatedData['reserved_until'];

        // there must not be any conflict with previous reservations
        foreach ($item->reservations as $reservation) {
            if ($reservation->conflictsWith($newReservation)) {
                // 409 Conflict
                abort(409, "reservation conflicts with a previous one: " .
                           $reservation->reserved_from . " " .
                           $reservation->reserved_until);
            }
        }

        // and if everything is alright:
        $newReservation->save();
        return response()->json($newReservation);
    }

    public function delete(Reservation $reservation) {
        $reservation->delete();
        return redirect(route('reservations.index', ['item' => $reservation->reservableItem]));
    }
}
