<?php

namespace App\Models\PeriodicEvents;

use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodicEvent extends Model
{
    protected $fillable = [
        'event_model',
        'start_date',
        'start_handled',
        'end_date',
        'extended_end_date',
        'end_handled',
        'show_until',
        'semester_id'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'extended_end_date' => 'datetime'
    ];

    public final function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * @return Carbon|null the start date of the current PeriodicEvent
     */
    public final function startDate(): ?Carbon
    {
        return Carbon::parse($this->start_date);
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    public final function endDate(): ?Carbon
    {
        return Carbon::parse($this->extended_end_date ?? $this->end_date);
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    public final function deadline(): ?Carbon
    {
        return $this->endDate();
    }

    /**
     * Check if the PeriodicEvent is currently active or not.
     * start date <= now <= (extended) end date
     * @return bool
     */
    public final function isActive(): bool
    {
        if($this->startDate()->isFuture()) return false;
        if($this->endDate()->isPast()) return false;
        return true;
    }

    /**
     * @return bool if the end date has been extended or not
     */
    public final function isExtended(): bool
    {
        return $this->extended_end_date != null;
    }


    /**
     * The function that listens for the different dates and handles them.
     */
    public static function listen(): void
    {
        foreach (PeriodicEvent::all() as $event) {
            if ($event->startDate()->isPast() && !$event->start_handled) {
                app($event->event_model)->handlePeriodicEventStart();
                $event->start_handled = now();
                $event->save(['timestamps' => false]);
            }
            //TODO reminders

            if ($event->endDate()->isPast() && !$event->end_handled) {
                app($event->event_model)->handlePeriodicEventEnd();
                $event->end_handled = now();
                $event->save(['timestamps' => false]);
            }
        }
    }
}
