<?php

namespace App\Listeners;

use App\Events\SemesterEvaluationPeriodStart;
use App\Mail\EvaluationFormAvailable;
use App\Mail\EvaluationFormAvailableDetails;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SemesterEvaluationPeriodStartListener
{
    /**
     * Handle the event.
     */
    public function handle(SemesterEvaluationPeriodStart $event): void
    {
        Mail::to(config('contacts.mail_membra'))->queue(new EvaluationFormAvailable($event->periodicEvent->deadline()));
        if (User::secretary()) {
            Mail::to(User::secretary())->queue(new EvaluationFormAvailableDetails(User::secretary()->name, $event->periodicEvent->deadline()));
        }
        if (User::president()) {
            Mail::to(User::president())->queue(new EvaluationFormAvailableDetails(User::president()->name, $event->periodicEvent->deadline()));
        }
    }
}
