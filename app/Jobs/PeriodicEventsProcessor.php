<?php

namespace App\Jobs;

use App\Models\PeriodicEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PeriodicEventsProcessor implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Handle the periodic events.
     * @see bootstrap/app.php for how often is this executed.
     */
    public function handle(): void
    {
        foreach (PeriodicEvent::all() as $event) {
            if ($event->startDate()->isPast() && !$event->start_handled) {
                $event->handleStart();
                Log::info('Periodic event started: ' . $event->event_model);
            } elseif ($event->isActive()) {
                $event->handleReminder();
                Log::info('Periodic event reminder: ' . $event->event_model . ' with ' . $days_left . ' days left');
            }
            if ($event->endDate()->isPast() && !$event->end_handled) {
                $event->handleEnd();
                Log::info('Periodic event ended: ' . $event->event_model);
            }
        }
    }
}
