<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

use App\Models\Reservations\ReservableItem;
use App\Models\Reservations\Reservation;

/**
 * A helper class representing a rectangle in the timetable,
 * based on CarbonImmutable instances
 * so that it cannot be accidentally modified
 * via outside references.
 */
class Block
{
    private CarbonImmutable $from;
    private CarbonImmutable $until;

    /**
     * The id of the reservation the block belongs to,
     * or null if it is an empty block.
     * Shall not be modified after construction.
     */
    private ?int $reservation_id;

    /**
     * The constructor.
     * Throws an InvalidArgumentException
     * if $from >= $until.
     */
    public function __construct(CarbonImmutable $from, CarbonImmutable $until, ?int $reservation_id)
    {
        if ($from >= $until) {
            throw new \InvalidArgumentException("start date of block not earlier than end date: $from, $until");
        } else {
            $this->from = $from;
            $this->until = $until;
            $this->reservation_id = $reservation_id;
        }
    }

    /**
     * Setter for the start date.
     * Throws if the new value would be later than or equal to the current end date.
     */
    public function setFrom(CarbonImmutable $from): void
    {
        if ($from >= $this->until) {
            throw new \InvalidArgumentException("new start date is not earlier than current end date: $from, {$this->until}");
        } else {
            $this->from = $from;
        }
    }
    /**
     * Setter for the end date.
     * Throws if the new value would be earlier than or equal to the current start date.
     */
    public function setUntil(CarbonImmutable $until): void
    {
        if ($until <= $this->from) {
            throw new \InvalidArgumentException("new end date is not later than current start date: {$this->from}, $until");
        } else {
            $this->until = $until;
        }
    }
    /**
     * Returns the start date.
     */
    public function getFrom(): CarbonImmutable
    {
        return $this->from;
    }
    /**
     * Returns the end date.
     */
    public function getUntil(): CarbonImmutable
    {
        return $this->until;
    }
    /**
     * Returns the reservation id.
     */
    public function getReservationId(): ?int
    {
        return $this->reservation_id;
    }
    /**
     * The reservation the block belongs to (or null if none).
     * Throws if the id is not null and not valid.
     */
    public function reservation(): ?Reservation
    {
        if (is_null($this->reservation_id)) {
            return null;
        } else {
            return Reservation::findOrFail($this->reservation_id);
        }
    }
    /**
     * Whether this is a free block
     * (i.e. it does not belong to a reservation, but represents a free interval).
     */
    public function isFree(): bool
    {
        return is_null($this->reservation_id);
    }

    /**
     * Sets the given date as the new end date
     * and returns a new block
     * with the given date as the start date and the original end date as the end date
     * (thereby effectively splitting it from the original block).
     * The new block has the same reservation id.
     * Throws \InvalidArgumentException if the given date is not inside the interval.
     */
    public function splitAt(CarbonImmutable $middle): Block
    {
        try {
            $newBlock = new Block($middle, $this->until, $this->reservation_id);
            $this->setUntil($middle);
            return $newBlock;
        } catch (\InvalidArgumentException) {
            throw new \InvalidArgumentException("given date not inside the block's interval: {$this->from}, $middle, {$this->until}");
        }
    }
}

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
     * The first hour to be displayed in the table, inclusive (defaults to 0).
     */
    public int $firstHour;
    /**
     * The last hour to be displayed in the table, exclusive (defaults to 24).
     */
    public int $lastHour;
    /**
     * Whether item names should be displayed in the header.
     */
    public bool $displayItemNames;
    /**
     * Whether the timetable is intended to be printed.
     * Only displays recurring, 1/7-day-frequency reservations
     * and hides unverified ones
     * if true.
     */
    public bool $isPrintVersion;

    /**
     * Contains the "blocks" (rectangles) to be displayed in a timetable
     * for all items in the currently set timespan.
     * A block contains a "from" and "until" time (in Carbon instances)
     * and a "reservation_id" if it belongs to a reservation
     * (for a free span, it is null).
     * It is a computed property.
     */
    public function getBlocksProperty(): array
    {
        return array_map(
            fn (ReservableItem $item) =>
                self::listOfBlocks(
                    $item,
                    CarbonImmutable::make($this->firstDay),
                    CarbonImmutable::make($this->lastDay->copy()->addDay()),
                    $this->isPrintVersion
                ),
            $this->items
        );
    }

    /**
     * Generates an ordered array of blocks for the given item in the given timespan.
     */
    private static function listOfBlocks(ReservableItem $item, CarbonImmutable $from, CarbonImmutable $until, bool $isPrintVersion): array
    {
        // for some reason, filtering messes up the indices; hence the use of array_values
        $reservations = array_values(
            $item->reservationsInSlot($from, $until)
            ->filter(function (Reservation $reservation) use ($isPrintVersion) {
                if (!user()->can('view', $reservation)) {
                    return false;
                } elseif ($isPrintVersion) {
                    return $reservation->verified
                        && $reservation->isRecurring()
                        && (7 == $reservation->group->frequency
                            || 1 == $reservation->group->frequency);
                } else {
                    return true;
                }
            })
            ->all()
        );

        $blocks = [];
        $isForReservation = 0 < count($reservations) && $reservations[0]->reserved_from <= $from;

        $currentStart = $from;
        $i = 0;
        while($i < count($reservations)) {
            if ($isForReservation) {
                $reservation = $reservations[$i];
                $blocks[] = new Block(
                    from: CarbonImmutable::make($reservation->reserved_from),
                    until: CarbonImmutable::make($reservation->reserved_until),
                    reservation_id: $reservation->id
                );
                $currentStart = CarbonImmutable::make($reservation->reserved_until);
                $isForReservation = false;
                ++$i;
            } else {
                $currentEnd = CarbonImmutable::make($reservations[$i]->reserved_from);
                if ($currentStart < $currentEnd) {
                    $blocks[] = new Block(
                        from: $currentStart,
                        until: $currentEnd,
                        reservation_id: null
                    );
                    $currentStart = $currentEnd;
                }
                $isForReservation = true;
            }
        }
        // for the last block:
        if ($currentStart < $until) {
            // create a final free block
            $blocks[] = new Block(
                from: $currentStart,
                until: $until,
                reservation_id: null
            );
        } else {
            // cut the end if it is after $until
            $blocks[count($blocks) - 1]->setUntil($until);
        }
        // for the first block
        // we have to do it here because we can be sure here that at least one element exists
        $blocks[0]->setFrom($from);

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

            // this has to be modifiable
            $splittingPointAfter = Carbon::make($block->getFrom());
            if ($block->isFree()) {
                $splittingPointAfter->minute = 0;
                $splittingPointAfter->addHours(1);
            } else {
                $splittingPointAfter->hour = 0;
                $splittingPointAfter->minute = 0;
                $splittingPointAfter->addDays(1);
            }

            if ($block->getUntil() <= $splittingPointAfter) {
                $result[] = $block;
                ++$i;
            } else {
                // that array won't be used for anything else anyway
                $blocks[$i] = $block->splitAt(CarbonImmutable::make($splittingPointAfter));
                $result[] = $block;
            }
        }

        return $result;
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
    public function mount(
        array $items,
        int $days,
        int $firstHour = 0,
        int $lastHour = 24,
        bool $displayItemNames = false,
        bool $isPrintVersion = false
    ) {
        if ($days < 1) {
            throw new \InvalidArgumentException();
        }

        $this->items = $items;

        if ($isPrintVersion) {
            // we will choose a Monday
            $this->firstDay = Carbon::today()->startOfWeek();
        } else {
            $this->firstDay = Carbon::today();
        }
        $this->lastDay = $this->firstDay->copy()->addDays($days - 1);

        $this->firstHour = $firstHour;
        $this->lastHour = $lastHour;
        $this->displayItemNames = $displayItemNames;
        $this->isPrintVersion = $isPrintVersion;
    }

    /**
     * The view to render the component with.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.timetable');
    }

    /**
     * Moves the selected interval with the given amount of days
     * (positive if towards the future).
     */
    public function step(int $days): void
    {
        $this->firstDay->addDays($days);
        $this->lastDay->addDays($days);
    }

    /**
     * Runs when the event 'first-day-updated'
     * is dispatched by the separate first_day_picker component.
     */
    #[On('first-day-updated')]
    public function firstDayUpdated(string $firstDay): void
    {
        $oldFirstDay = $this->firstDay;
        $this->firstDay = Carbon::make($firstDay);
        $this->lastDay->addDays($oldFirstDay->diffInDays($this->firstDay));
    }
}
