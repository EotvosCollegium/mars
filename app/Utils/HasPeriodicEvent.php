<?php

namespace App\Utils;

use App\Jobs\PeriodicEventsProcessor;
use App\Models\PeriodicEvent;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Add this trait to controllers that is connected to periodic events.
 * Status changes and events are handled automatically.
 *
 * Usage:
 * Use the periodicEvent() method or the other getters to get the current PeriodicEvent's data.
 * Use the updatePeriodicEvent() method to create or update the current PeriodicEvent.
 * Overwrite handlePeriodicEventStart() and handlePeriodicEventEnd() methods to attach actions for these events.
 *
 * @see PeriodicEvent
 * @see PeriodicEventsProcessor
 */
trait HasPeriodicEvent
{
    /**
     * Get the current PeriodicEvent connected to the model.
     * It returns the most recent event that is still visible.
     * Visibility means that it has its `show_until` or some deadline in the future.
     *
     * Note: a future event may be returned, if exists.
     *
     * @return PeriodicEvent|null
     */
    final public function periodicEvent(): ?PeriodicEvent
    {
        return PeriodicEvent::where('event_model', self::class)
            ->where(function ($query) {
                $query
                    ->orWhere('extended_end_date', '>=', now())
                    ->orWhere(function ($query) {
                        $query
                            ->whereNull('extended_end_date')
                            ->where('end_date', '>=', now());
                    })
                    ->orWhere('show_until', '>=', now());
            })
            ->orderBy('start_date', 'desc')
            ->first();
    }

    /**
     * Create or update the current PeriodicEvent connected to the model.
     *
     * Note: current event is overwritten, if exists.
     * To add a new event, wait until the previous one becomes obsolete.
     *
     * @param array $data the PeriodicEvent's attributes.
     * @return PeriodicEvent
     */
    final public function updatePeriodicEvent(array $data): PeriodicEvent
    {
        $event = $this->periodicEvent();
        if($event) {
            $event->update($data);
            $event->refresh();
            //TODO reset _handled fields
        } else {
            $event = PeriodicEvent::create(array_merge(['event_model' => self::class], $data));
        }
        return $event;
    }

    /**
     * Handle periodic event start event.
     */
    public function handlePeriodicEventStart(): void
    {
        // Do nothing by default
    }

    /**
     * Handle periodic event end event.
     */
    public function handlePeriodicEventEnd(): void
    {
        // Do nothing by default
    }

    /**
     * Check if the PeriodicEvent is currently active or not.
     * start date <= now <= (extended) end date
     * @return bool
     */
    final public function isActive(): bool
    {
        return $this->periodicEvent()?->isActive() ?? false;
    }

    /**
     * @return bool if the end date has been extended or not
     */
    final public function isExtended(): bool
    {
        return $this->periodicEvent()?->isExtended() ?? false;
    }

    /**
     * @return Carbon|null the start date of the current PeriodicEvent
     */
    final public function getStartDate(): ?Carbon
    {
        return $this->periodicEvent()?->startDate();
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    final public function getEndDate(): ?Carbon
    {
        return $this->periodicEvent()?->endDate();
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    final public function getDeadline(): ?Carbon
    {
        return $this->getEndDate();
    }

    /**
     * @return Semester|BelongsTo|null the semester connected to the current PeriodicEvent
     */
    final public function semester(): Semester|BelongsTo|null
    {
        return $this->periodicEvent()?->semester;
    }
}
