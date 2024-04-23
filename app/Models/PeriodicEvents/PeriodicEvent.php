<?php

namespace App\Models\PeriodicEvents;

use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    public function realEndDate(): Attribute
    {
        return Attribute::make(
            get: function (): Carbon {
                return Carbon::parse($this->extended_end_date ?? $this->end_date);
            }
        );
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Check if the PeriodicEvent is currently active or not.
     * start date <= now <= (extended) end date
     * @return bool
     */
    public function isActive(): bool
    {
        if(Carbon::parse($this->start_date)->isFuture()) return false;
        if(Carbon::parse($this->real_end_date)->isPast()) return false;
        return true;
    }

    /**
     * @return bool if the end date has been extended or not
     */
    public function isExtended(): bool
    {
        return $this->extended_end_date != null;
    }


    /**
     * The function that listens for the different dates and handles them.
     */
    public static function listen(): void
    {
        //TODO fire events
        foreach (PeriodicEvent::all() as $event) {
            if (Carbon::parse($event->start_date)->isPast() && !$event->start_handled) {
                app($event->event_model)::handleStart();
                $event->start_handled = now();
                $event->save(['timestamps' => false]);
            }
            //TODO reminders

            if (Carbon::parse($event->real_end_date)->isPast() && !$event->end_handled) {
                app($event->event_model)::handleEnd();
                $event->end_handled = now();
                $event->save(['timestamps' => false]);
            }
        }
    }
}
