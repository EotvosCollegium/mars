<?php

namespace App\Models\EventTriggers;

use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Models\Semester;
use Carbon\Carbon;

class DeactivateStatus implements EventTriggerInterface
{
    /**
     * The statuses got deactivated at the last day of the semester.
     */
    public function nextDate(): Carbon
    {
        $date = Semester::current()->getEndDate()->subDay();
        if($date->gt(Carbon::now())) {
            return $date;
        } else {
            return Semester::next()->getEndDate()->subDay();
        }

    }

    /**
     * Send email notification.
     */
    public function handle()
    {
        SemesterEvaluationController::finalizeStatements();
    }
}
