<?php

namespace App\Models\EventTriggers;

use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Models\Semester;
use Carbon\Carbon;

class SemesterEvaluationAvailable implements EventTriggerInterface
{
    /**
     * The evaluation is available 2 months before the end of the semester.
     */
    public function nextDate(): Carbon
    {
        $date = Semester::current()->getEndDate()->subMonths(2);
        if ($date->gt(Carbon::now())) {
            return $date;
        } else {
            return Semester::next()->getEndDate()->subMonths(2);
        }

    }

    /**
     * Send email notification.
     */
    public function handle()
    {
        SemesterEvaluationController::sendEvaluationAvailableMail();
    }

    public function remindBeforeDays(): ?int
    {
        return null;
    }

    public function handleReminder(): void
    {
        return;
    }
}
