<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ReservableItem;

class ReservableItemController extends Controller
{
    public function index(Request $request) {
        $type = $request->type;
        if (is_null($type)) {
            return response()->json(ReservableItem::all()->get());
        } else if ('washing_machine' == $type) {
            return response()->json(ReservableItem::where('type', 'washing_machine')->get());
        } else if ('room' == $type) {
            return response()->json(ReservableItem::where('type', 'room')->get());
        } else {
            abort(400, "Unknown reservable item type: $type");
        }
    }

    public function show(ReservableItem $item) {
        return response()->json($item);
    }

    public function create() {
        return response()->json(["itt lesz a szép form"]);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
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

        $validatedData = $validator->validate();

        $newItem = ReservableItem::create($validatedData);

        return response()->json($newItem);
    }

    public function delete(ReservableItem $item) {
        $item->delete();
        return redirect(route('reservations.items.index'));
    }
}
