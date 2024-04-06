<?php

namespace App\Models\PeriodicEvents;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractPeriodicEvent extends Model
{
    public static function periodicEvent(): PeriodicEvent
    {
        return PeriodicEvent::where('event_model', static::class)->orderBy('created_at', 'desc')->first();
    }


    public function isActive()
    {
        if(!$this->periodicEvent()) return false;
        if(Carbon::parse($this->periodicEvent()->start_date)->isFuture()) return false;
        if(Carbon::parse($this->periodicEvent()->real_end_date)->isPast()) return false;
        return true;
    }
    /**
     * Handle event start.
     */
    public function handleStart(): void
    {
        // do nothing by default
    }

    /**
     * Handle event end or deadline.
     */
    public function handleEnd(): void
    {
        // do nothing by default
    }

    //TODO reminders
}
