<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Carbon\Carbon;

use App\Models\ReservableItem;

class ReservableItemController extends Controller
{
    /**
     * Lists reservable items (by default, both types).
     */
    public function index(Request $request) {
        $this->authorize('viewAny', ReservableItem::class);

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
     * Creates an ordered array of blocks to be displayed in a timetable
     * for a given item and timespan.
     * A block contains a "from" and "until" time (in Carbon instances)
     * and a "reservation_id" if it belongs to a reservation
     * (for a free span, it is null).
     */
    public static function listOfBlocks(ReservableItem $item, Carbon $from, Carbon $until): array {
        $reservations = $item->reservationsInSlot($from, $until)->all();

        $blocks = [];
        // whether the currently created block is for a reservation or for a free span
        $isForReservation = 0 < count($reservations) && $reservations[0]->reserved_from <= $from;
        $currentStart = $from;
        $i = 0;
        while ($i < count($reservations)) {
            $block = [];
            $block["from"] = $currentStart;
            if ($isForReservation) {
                $block["until"] = $reservations[$i]->reserved_until;
                $block["reservation_id"] = $reservations[$i]->id;
                $blocks[] = $block;
                $currentStart = $block["until"];
                $isForReservation = false;
                ++$i;
            } else if ($currentStart == $reservations[$i]->reserved_from) {
                // that means there is no free span until the next reservation
                $isForReservation = true;
            } else {
                $block["until"] = $reservations[$i]->reserved_from;
                $block["reservation_id"] = null;
                $blocks[] = $block;
                $currentStart = $block["until"];
                $isForReservation = true;
            }
        }

        if ($currentStart < $until) {
            // in the end, if we still have a free span after the last reservation:
            $block = [];
            $block["from"] = $currentStart;
            $block["until"] = $until;
            $block["reservation_id"] = null;
            $blocks[] = $block;
        } else {
            // we cut down the part that is after $until
            $blocks[count($blocks)-1]["until"] = $until;
        }

        // and we have to split those that span through midnight:
        $splitBlocks = [];
        $i = 0;
        while ($i<count($blocks)) {
            $block=$blocks[$i];
            $endOfThatDay = Carbon::make($block["from"])->copy();
            $endOfThatDay->hour = 0; $endOfThatDay->minute = 0;
            $endOfThatDay->addDays(1);
            if ($block["until"] <= $endOfThatDay) {
                $newBlock = [];
                $newBlock["from"] = Carbon::make($block["from"]);
                $newBlock["until"] = Carbon::make($block["until"]);
                $newBlock["reservation_id"] = $block["reservation_id"];
                $splitBlocks[]=$newBlock;
                ++$i;
            } else {
                $newBlock = [];
                $newBlock["from"] = Carbon::make($block["from"]);
                $newBlock["until"] = $endOfThatDay;
                $newBlock["reservation_id"] = $block["reservation_id"];
                $splitBlocks[]=$newBlock;
                // we can actually modify $blocks
                // as we won't use it again
                $blocks[$i]["from"]=$endOfThatDay;
            }
        }

        return $splitBlocks;
    }

    /**
     * Shows the details of a reservable item,
     * with the blocks of the timetable
     * in an array called "blocks".
     */
    public function show(ReservableItem $item) {
        $this->authorize('viewAny', ReservableItem::class);

        // for now:
        $from = Carbon::today()->startOfWeek();
        $until = $from->copy()->addDays(7);
        return view('reservations.items.show', [
            'item' => $item,
            'from' => $from,
            'until' => $until,
            'blocks' => ReservableItemController::listOfBlocks($item, $from, $until)
        ]);
    }

    /**
     * Shows a form for creating a new item.
     */
    public function create() {
        $this->authorize('administer', ReservableItem::class);

        return response()->json(["itten lesz a szép űrlap"]);
    }

    /**
     * Saves a new item from the data of the request,
     * after validation.
     */
    public function store(Request $request) {
        $this->authorize('administer', ReservableItem::class);

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
        $this->authorize('administer', ReservableItem::class);

        // TODO: notify people who have reserved it

        $item->delete();

        return redirect(route('reservations.items.index'));
    }

}
