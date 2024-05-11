<?php

namespace App\Jobs;

use App\Models\PeriodicEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PeriodicEventsProcessor implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
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
