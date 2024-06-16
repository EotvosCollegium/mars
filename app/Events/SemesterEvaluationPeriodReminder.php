<?php

namespace App\Events;

use App\Models\PeriodicEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SemesterEvaluationPeriodReminder
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public PeriodicEvent $periodicEvent;

    /**
     * Create a new event instance.
     */
    public function __construct(PeriodicEvent $periodicEvent)
    {
        $this->periodicEvent = $periodicEvent;
    }
}
