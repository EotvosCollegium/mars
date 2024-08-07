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

        // We also have to split blocks
        // that spill through midnights.
        // For washing machines, we even split them every hour;
        // for rooms, we only do so for free blocks.
        $splitBlocks = [];
        $i = 0;
        while ($i < count($blocks)) {
            $block = $blocks[$i];

            $splittingPointAfter = $block['from']->copy();
            if ('washing_machine' == $item->type || is_null($block['reservation_id'])) {
                $splittingPointAfter->minute = 0;
                $splittingPointAfter->addHours(1);
            } else {
                $splittingPointAfter->hour = 0;
                $splittingPointAfter->minute = 0;
                $splittingPointAfter->addDays(1);
            }

            if ($block['until'] <= $splittingPointAfter) {
                $splitBlocks[] = $block;
                ++$i;
            } else {
                $splitBlock = [
                    'from' => $block['from'],
                    'until' => $splittingPointAfter,
                    'reservation_id' => $block['reservation_id']
                ];
                $splitBlocks[] = $splitBlock;
                // that array won't be used for anything else anyway
                $blocks[$i]['from'] = $splittingPointAfter;
            }
        }

        return $splitBlocks;
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
        $from = Carbon::createMidnightDate($this->firstDay->year, $this->firstDay->month, $this->firstDay->day);
        $until = Carbon::createMidnightDate($this->lastDay->year, $this->lastDay->month, $this->lastDay->day);
        // because this is not inclusive:
        $until->addDay();

        $this->blocks = array_map(function (ReservableItem $item) use ($from, $until) {
            return self::listOfBlocks($item, $from, $until);
        }, $this->items);
    }

    /**
     * Gets the data from the @livewire parameters and sets the component properties.
     */
    public function mount(ReservableItem $item)
    {
        $this->item = $item;
        
        $this->firstDay = Carbon::today();
        $this->lastDay = Carbon::today()->addDays(2);

        $this->calculateBlocks();
    }

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
