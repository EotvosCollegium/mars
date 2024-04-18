<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Models\ReservableItem;

class ReservableItemController extends Controller
{
    /**
     * Lists reservable items (by default, both types).
     */
    public function index(Request $request) {
        $type = $request->type;
        if ("washing_machine" == $type) {
            $items = ReservableItem::where("type", "washing_machine")->get();
        } else if ("room" == $type) {
            $items = ReservableItem::where("type", "room")->get();
        } else if (is_null($type)) {
            $items = ReservableItem::orderBy("type")->get();
        } else {
            abort(400, "unknown reservable item type");
        }
        return view('reservations.items.index',  [
            'items' => $items,
            'title' => is_null($type) ? 'reservations.all_items' : 'reservations.' . $type
        ]);
    }

    /**
     * Shows the details of a reservable item.
     */
    public function show(ReservableItem $item) {
        return response()->json($item);
    }

    /**
     * Shows a form for creating a new item.
     */
    public function create() {
        return response()->json(["itten lesz a szép űrlap"]);
    }

    /**
     * Saves a new item from the data of the request,
     * after validation.
     */
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => Rule::in(["washing_machine", "room"]),
            'default_reservation_duration' => 'required|numeric|min:1|max:65535',
            'is_default_compulsory' => 'required|boolean',
            'allowed_starting_minutes' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $minutes = explode(',', $value);
                    foreach ($minutes as $minute) {
                        if (!is_numeric($minute) || intval($minute) < 0 || intval($minute) > 59) {
                            $fail("Invalid \"allowed starting minutes\" list (there is a value that is either not numeric or is not between 0 and 59).");
                        }
                    }
                },
            ],
            'out_of_order_from' => 'nullable|date',
            'out_of_order_until' => 'nullable|date'
        ]);
        /*
        $validator->after(function ($validator) {
            if (strtotime($request->out_of_order_from) > strtotime($request->out_of_order_until)) {
                $validator->errors()->add(
                    'out_of_order_until', 'Maintenence cannot end before its start!'
                );
            }
        });
        */
        
        //return response()->json($request->all());
        $validatedData = $validator->validate();
        //return response()->json($validatedData);

        $newItem = ReservableItem::create($validatedData);

        return response()->json($newItem);
    }

    public function delete(ReservableItem $item)
    {
        //$this->authorize('delete', ReservableItem::class);

        // notify people who have reserved it

        $item->delete();

        return redirect(route('reservations.items.index'));
    }

}
