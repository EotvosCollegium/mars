<?php

namespace App\Listeners;

use App\Events\SemesterEvaluationPeriodStart;
use App\Mail\EvaluationFormReminder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SemesterEvaluationPeriodReminderListener
{
    /**
     * Handle the event.
     */
    public function handle(SemesterEvaluationPeriodStart $event): void
    {
        $days_left = (int)$event->periodicEvent->endDate()->diffInDays(now()) * (-1);
        if($days_left == 2 || $days_left == 0) {
            Mail::to(config('contacts.mail_membra'))->queue(new EvaluationFormReminder($event->periodicEvent->deadline()));
        }
    }
}
