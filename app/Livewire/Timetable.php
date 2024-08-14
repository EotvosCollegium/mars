<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;

use App\Models\ReservableItem;

/**
 * A Livewire component displaying an interactive timetable of current reservations
 * in a given interval.
 */
class Timetable extends Component
{
    /**
     * The items whose reservations are being displayed.
     */
    public array $items;
    /**
     * The first day of the span displayed (inclusive).
     */
    public Carbon $firstDay;
    /**
     * The last day of the span displayed (inclusive).
     */
    public Carbon $lastDay;
    /**
     * Whether item names should be displayed in the header.
     */
    public bool $displayItemNames;

    /**
     * Contains the "blocks" (rectangles) to be displayed in the timetable.
     */
    private array $blocks;

    /**
     * Generates an ordered array of blocks for the given item in the given timespan.
     */
    private static function listOfBlocks(ReservableItem $item, Carbon $from, Carbon $until): array
    {
        $reservations = $item->reservationsInSlot($from, $until)->all();

        $blocks = [];
        $isForReservation = 0 < count($reservations) && $reservations[0]->reserved_from <= $from;
        $currentStart = $from;
        $i = 0;
        while($i < count($reservations)) {
            if ($isForReservation) {
                $reservation = $reservations[$i];
                $blocks[] = [
                    'from' => Carbon::make($reservation->reserved_from),
                    'until' => Carbon::make($reservation->reserved_until),
                    'reservation_id' => $reservation->id
                ];
                $currentStart = Carbon::make($reservation->reserved_until);
                $isForReservation = false;
                ++$i;
            } else {
                $currentEnd = Carbon::make($reservations[$i]->reserved_from);
                if ($currentStart < $currentEnd) {
                    $blocks[] = [
                        'from' => $currentStart,
                        'until' => $currentEnd,
                        'reservation_id' => null
                    ];
                    $currentStart = $currentEnd;
                }
                $isForReservation = true;
            }
        }
        // for the last block:
        if ($currentStart < $until) {
            // create a final free block
            $blocks[] = [
                'from' => $currentStart,
                'until' => $until,
                'reservation_id' => null
            ];
        } else {
            // cut the end if it is after $until
            $blocks[count($blocks) - 1]['until'] = $until;
        }

        return self::splitBlocks($blocks);
    }

    /**
     * Takes a list of blocks created previously
     * and splits those that spill through midnights.
     * Free blocks also get split every hour.
     */
    private static function splitBlocks(array $blocks): array
    {
        $result = [];
        $i = 0;
        while ($i < count($blocks)) {
            $block = $blocks[$i];

            $splittingPointAfter = $block['from']->copy();
            if (is_null($block['reservation_id'])) {
                $splittingPointAfter->minute = 0;
                $splittingPointAfter->addHours(1);
            } else {
                $splittingPointAfter->hour = 0;
                $splittingPointAfter->minute = 0;
                $splittingPointAfter->addDays(1);
            }

            if ($block['until'] <= $splittingPointAfter) {
                $result[] = $block;
                ++$i;
            } else {
                $result[] = [
                    'from' => $block['from'],
                    'until' => $splittingPointAfter,
                    'reservation_id' => $block['reservation_id']
                ];
                // that array won't be used for anything else anyway
                $blocks[$i]['from'] = $splittingPointAfter;
            }
        }

        return $result;
    }

    /**
     * Sets $this->blocks to be an ordered array of blocks to be displayed in a timetable
     * for all items in the currently set timespan.
     * A block contains a "from" and "until" time (in Carbon instances)
     * and a "reservation_id" if it belongs to a reservation
     * (for a free span, it is null).
     */
    private function calculateBlocks(): void
    {
        // we can safely assume these are midnight dates
        $this->blocks = array_map(
            fn (ReservableItem $item) =>
                self::listOfBlocks($item, $this->firstDay, $this->lastDay->copy()->addDay()),
            $this->items);
    }

    /**
     * Gets the data from the @livewire parameters and sets the component properties.
     * The first parameter contains the items to be displayed,
     * the second the number of days to be displayed at once
     * (which must be positive).
     * The third, optional parameter sets
     * whether item names should be displayed
     * (it is by default false).
     */
    public function mount(array $items, int $days, bool $displayItemNames = false)
    {
        if ($days < 1) throw new \InvalidArgumentException;

        $this->items = $items;

        $this->firstDay = Carbon::today();
        $this->lastDay = $this->firstDay->copy()->addDays($days - 1);

        $this->displayItemNames = $displayItemNames;

        $this->calculateBlocks();
    }

    /**
     * The view to render the component with.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.timetable', [
            'firstDay' => $this->firstDay,
            'lastDay' => $this->lastDay,
            'blocks' => $this->blocks,
        ]);
    }

    /**
     * Moves the selected interval with the given amount of days
     * (positive if towards the future).
     */
    public function step(int $days)
    {
        $this->firstDay->addDays($days);
        $this->lastDay->addDays($days);

        $this->calculateBlocks();
    }
}
