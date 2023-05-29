<?php

namespace App\Models;

use App\Models\EventTriggers\DeactivateStatus;
use App\Models\EventTriggers\EventTriggerInterface;
use App\Models\EventTriggers\SemesterEvaluationAvailable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * This class implements the logic of triggering certain (recurring) events (eg. automatic status changes)
 * when we reach a given datetime. The triggers will fire a signal that we handle accordingly.
 * Members of this models should not get created through the site. It is stored in the database
 * so the dates can be changed on the run, everything else should be static.
 * The handlers of each signal will do the following:
 *  - Runs the function/does the changes relvant to the event.
 *  - Updates the trigger date.
 */
class EventTrigger extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'signal';
    protected $fillable = [
        'name', 'date', 'signal', 'comment',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    // Signal for notifying the students that the evaluation form is available.
    public const SEMESTER_EVALUATION_AVAILABLE = 1;
    // Deadline for the above signal; when triggered, everyone who did not make a
    // status statement will be set to alumni.
    public const DEACTIVATE_STATUS_SIGNAL = 2;
    public const SIGNALS = [
        self::SEMESTER_EVALUATION_AVAILABLE,
        self::DEACTIVATE_STATUS_SIGNAL,
    ];

    /**
     * The function that is called by the kernel (based on cronejob).
     * Handles the signals that are due.
     */
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

    /**
     * Get the object for the signal.
     *
     * @return EventTriggerInterface
     */
    public function getTrigger(): EventTriggerInterface
    {
        switch ($this->signal) {
            case self::SEMESTER_EVALUATION_AVAILABLE:
                return new SemesterEvaluationAvailable();
            case self::DEACTIVATE_STATUS_SIGNAL:
                return new DeactivateStatus();
            default:
                throw new \Exception("Unknown signal: " . $this->signal);
        }
    }


    /**
     * Hande a signal and update the date.
     */
    public function handleSignal()
    {
        $trigger = $this->getTrigger();
        DB::transaction(function () use ($trigger) {
            $trigger->handle();
            $this->update(['date' => $trigger->nextDate()]);
        });
    }
}
