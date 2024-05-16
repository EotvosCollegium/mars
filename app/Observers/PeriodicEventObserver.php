<?php

namespace App\Observers;

use App\Models\PeriodicEvent;

class PeriodicEventObserver
{
    /**
     * Handle the PeriodicEvent "created" event.
     */
    public function created(PeriodicEvent $periodicEvent): void
    {
        //
    }

    /**
     * Handle the PeriodicEvent "updated" event.
     */
    public function updated(PeriodicEvent $periodicEvent): void
    {
        if($periodicEvent->startDate()->isFuture()) {
            $periodicEvent->start_handled = null;
        }
        if($periodicEvent->endDate()->isFuture()) {
            $periodicEvent->end_handled = null;
        }
    }

    /**
     * Handle the PeriodicEvent "deleted" event.
     */
    public function deleted(PeriodicEvent $periodicEvent): void
    {
        //
    }

    /**
     * Handle the PeriodicEvent "restored" event.
     */
    public function restored(PeriodicEvent $periodicEvent): void
    {
        //
    }

    /**
     * Handle the PeriodicEvent "force deleted" event.
     */
    public function forceDeleted(PeriodicEvent $periodicEvent): void
    {
        //
    }
}
