<?php

namespace App\Models\PeriodicEvents;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class PeriodicEvent extends Model
{
    protected $fillable = [
        'event_model',
        'start_date',
        'start_handled',
        'end_date',
        'extended_end_date',
        'end_handled'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'extended_end_date' => 'datetime'
    ];

    public function realEndDate(): Attribute
    {
        return Attribute::make(
            get: function (): string|null {
                return $this->extended_end_date ?? $this->end_date;
            }
        );
    }


    /**
     * The function that listens for the different dates and handles them.
     */
    public static function listen(): void
    {
        foreach (PeriodicEvent::all() as $event) {
            if (Carbon::parse($event->start_date)->isPast() && !$event->start_handled) {
                app($event->event_model)->handleStart();
                $event->start_handled = now();
                $event->save(['timestamps' => false]);
            }
            //TODO reminders

            if (Carbon::parse($event->real_end_date)->isPast() && !$event->end_handled) {
                app($event->event_model)->handleEnd();
                $event->end_handled = now();
                $event->save(['timestamps' => false]);
            }
        }
    }
}
