<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use Carbon\Carbon;

use App\Models\ReservableItem;

class ReservableItemController extends Controller
{
    public function index(Request $request) {
        $this->authorize('viewAny', ReservableItem::class);

        $type = $request->type;
        if (is_null($type)) {
            $items = ReservableItem::all();
        } else if ('washing_machine' == $type) {
            $items = ReservableItem::where('type', 'washing_machine')->get();
        } else if ('room' == $type) {
            $items = ReservableItem::where('type', 'room')->get();
        } else {
            abort(400, "Unknown reservable item type: $type");
        }
        return view('reservations.items.index', [
            'items' => $items
        ]);
    }
  /**
   * Shows the details of a reservable item.
   * Creates an ordered array of blocks to be displayed in a timetable
   * for a given item and timespan.
   * A block contains a "from" and "until" time (in Carbon instances)
   * and a "reservation_id" if it belongs to a reservation
   * (for a free span, it is null).
   */
    public static function listOfBlocks(ReservableItem $item, Carbon $from, Carbon $until): array {
        $reservations = $item->reservationsInSlot($from, $until)->all();

        $blocks = [];
        $isForReservation = 0 < count($reservations) && $reservations[0]->reserved_from <= $from;
        $currentStart = $from;
        $i = 0;
        while($i < count($reservations)) {
            $block = [];
            if ($isForReservation) {
                $reservation = $reservations[$i];
                $block['from'] = Carbon::make($reservation->reserved_from);
                $block['until'] = Carbon::make($reservation->reserved_until);
                $block['reservation_id'] = $reservation->id;
                $blocks[] = $block;
                $currentStart = $block['until'];
                $isForReservation = false;
                ++$i;
            } else {
                $currentEnd = Carbon::make($reservations[$i]->reserved_from);
                if ($currentStart < $currentEnd) {
                    $block['from'] = $currentStart;
                    $block['until'] = $currentEnd;
                    $block['reservation_id'] = null;
                    $currentStart = $currentEnd;
                    $blocks[] = $block;
                }
                $isForReservation = true;
            }
        }
        // for the last block:
        if ($currentStart < $until) {
            // create a final free block
            $block = [
                'from' => $currentStart,
                'until' => $until,
                'reservation_id' => null
            ];
            $blocks[] = $block;
        } else {
            // cut the end if it is after $until
            $blocks[count($blocks)-1]['until'] = $until;
        }

        // we also have to split blocks
        // that spill through midnights
        $splitBlocks = [];
        $i = 0;
        while ($i < count($blocks)) {
            $block = $blocks[$i];

            $midnightAfter = $block['from']->copy();
            $midnightAfter->hour = 0;
            $midnightAfter->minute = 0;
            $midnightAfter->addDays(1);

            if ($block['until'] <= $midnightAfter) {
                $splitBlocks[] = $block;
                ++$i;
            } else {
                $splitBlock = [
                    'from' => $block['from'],
                    'until' => $midnightAfter,
                    'reservation_id' => $block['reservation_id']
                ];
                $splitBlocks[] = $splitBlock;
                // that array won't be used for anything else anyway
                $blocks[$i]['from'] = $midnightAfter;
            }
        }

        return $splitBlocks;
    }

    public function show(ReservableItem $item) {
        $this->authorize('viewAny', ReservableItem::class);

        $from = Carbon::today()->startOfWeek();
        $until = $from->copy()->addDays(7);
        return view('reservations.items.show', [
            'item' => $item,
            'from' => $from,
            'until' => $until,
            'blocks' => ReservableItemController::listOfBlocks($item, $from, $until)
        ]);
    }

    public function create() {
        $this->authorize('administer', ReservableItem::class);

        abort(500, 'create not implemented yet');
    }

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
            'out_of_order' => 'nullable|boolean',
        ]);

        $validatedData = $validator->validate();

        $newItem = ReservableItem::create($validatedData);

        return redirect()->route('reservations.items.show', $newItem);
    }

    public function delete(ReservableItem $item) {
        $this->authorize('administer', ReservableItem::class);

        $item->delete();
        return redirect(route('reservations.items.index'));
    }
}
