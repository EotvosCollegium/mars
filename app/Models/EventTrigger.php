<?php

namespace App\Models;

use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * This class implements the logic of triggering certain (recurring) events (eg. automatic status changes)
 * when we reach a given datetime. The triggers will fire a signal that we handle accordingly.
 * Members of this models should not get created through the site. It is stored in the database
 * so the dates can be changed on the run, everything else should be static.
 * The handlers of each signal will do one or two things:
 *  - Runs the function/does the changes relvant to the event.
 *  - (only recurring events) Updates the trigger date.
 */
class EventTrigger extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'signal';
    protected $fillable = [
        'name', 'data', 'date', 'signal', 'comment',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // Signal for setting the default activation date to the next semester.
    public const INTERNET_ACTIVATION_SIGNAL = 0;
    // Signal for notifying the students to make a statement regarding their status
    // in the next semester.
    public const SEMESTER_EVALUATION_AVAILABLE = 1;
    // Deadline for the above signal; when triggered, everyone who did not make a
    // statement will be set to inactive.
    public const DEACTIVATE_STATUS_SIGNAL = 2;
    public const SIGNALS = [
        self::INTERNET_ACTIVATION_SIGNAL,
        self::SEMESTER_EVALUATION_AVAILABLE,
        self::DEACTIVATE_STATUS_SIGNAL,
    ];

    public static function listen()
    {
        $now = Carbon::now();
        $events = EventTrigger::where('date', '<=', $now)
                              ->get();
        foreach ($events as $event) {
            $event->handleSignal();
        }

        return $events;
    }

    /* Getters */

    public static function internetActivationDeadline()
    {
        return self::find(self::INTERNET_ACTIVATION_SIGNAL)->date;
    }


    /* Handlers which are fired when the set date is reached. */

    public function handleSignal()
    {
        switch ($this->signal) {
            case self::INTERNET_ACTIVATION_SIGNAL:
                $this->handleInternetActivationSignal();
                break;
            case self::SEMESTER_EVALUATION_AVAILABLE:
                $this->handleSemesterEvaluationAvailable();
                break;
            case self::DEACTIVATE_STATUS_SIGNAL:
                $this->deactivateStatus();
                break;
            default:
                Log::warning('Event Trigger got undefined signal: '.$this->signal);
                break;
        }

        return $this;
    }

    private function handleInternetActivationSignal()
    {
        $this->update([
            // Update the new trigger date
            'date' => Semester::next()->getStartDate()->addMonth(1),
            'data' => null,
        ]);
    }

    private function handleSemesterEvaluationAvailable()
    {
        DB::transaction(function () {
            // Send notification
            SemesterEvaluationController::sendEvaluationAvailableMail();

            $this->update([
                // Update the new trigger date
                'date' => Semester::next()->getEndDate()->subMonth(2),
            ]);
        });
    }

    private function deactivateStatus()
    {
        DB::transaction(function () {
            // Triggering the event
            SemesterEvaluationController::finalizeStatements();

            $this->update([
                // Update the new trigger date
                'date' => Semester::next()->getEndDate()->subDay(1)
            ]);
        });

    }
}
