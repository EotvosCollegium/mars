<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\ReservableItem;
use App\Models\Reservation;

class ReservationController extends Controller
{
    /**
     * Lists reservations for a given item.
     */
    public function index(ReservableItem $item)
    {
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
        $machines = ReservableItem::where('type', 'washing_machine')
                                    ->get()
                                    ->map(function (ReservableItem $machine) {
            return [
                'reservable_item' => $machine,
                'reservation' => Reservations::where('reservable_item_id', $machine->id)
                                               ->orderBy('reserved_from')
                                               ->get()
            ];
        });
    }

    /**
     * Lists the details of a reservation.
     */
    public function show(Reservation $reservation)
    {
        return response()->json($reservation);
    }

    /**
     * Returns a form for creating a reservation.
     */
    public function create(ReservableItem $item)
    {
        abort(500, 'create not implemented yet');
    }

    /**
     * Stores a reservation based on
     * a ReservableItem provided separately
     * and the data in the request.
     */
    public function store(ReservableItem $item, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2047',
            'reserved_from' => 'required|date',
            'reserved_until' => 'required|date'
        ]);

        $validatedData = $validator->validate();

        // we do not save it yet!
        $newReservation = new Reservation();
        $newReservation->reservable_item_id = $item->id;
        $newReservation->user_id = user()->id;
        $newReservation->title = $validatedData['title'];
        $newReservation->note = $validatedData['note'];
        $newReservation->reserved_from = $validatedData['reserved_from'];
        $newReservation->reserved_until = $validatedData['reserved_until'];

        // TODO: check whether it conflicts with anything

        // and finally:
        $newReservation->save();

        return response()->json($newReservation);
    }
}
