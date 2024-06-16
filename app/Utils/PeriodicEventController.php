<?php

namespace App\Utils;

use App\Http\Controllers\Controller;
use App\Jobs\PeriodicEventsProcessor;
use App\Models\PeriodicEvent;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Special controllers that are connected to periodic events.
 * A periodic event defined in the constructor will be handled and used by the controller.
 * Status changes and events are handled automatically (register them in PeriodicEvent).
 *
 * Through the functions of this class, we can check if the event is active or not, get the deadline, etc.
 * We can set up actions that will be executed when the event starts or ends.
 * The PeriodicEvent is also attached to a semester. The controller should use that semester
 * (through the periodicEvent) to avoid conflicts when semesters change.
 * Store that semester in the related models.
 *
 * @warning Be aware that the PeriodicEvent's data and semester gets overwritten every iteration.
 *
 * Usage:
 * Use the periodicEvent() method or the other getters to get the PeriodicEvent's data.
 * Use the updatePeriodicEvent() method to create or update the current PeriodicEvent.
 * Overwrite handlePeriodicEventStart() and handlePeriodicEventEnd() methods to attach actions for these events.
 *
 * @see PeriodicEvent
 * @see PeriodicEventsProcessor
 */
abstract class PeriodicEventController extends Controller
{
    protected string $periodicEventName;

    public function __construct(string $periodicEventName)
    {
        $this->periodicEventName = $periodicEventName;
    }

    /**
     * Get the last PeriodicEvent connected to the controller.
     *
     * @return PeriodicEvent|null
     */
    final public function periodicEvent(): ?PeriodicEvent
    {
        return PeriodicEvent::where('event_model', $this->periodicEventName)
            //ensure we only get one event
            ->orderBy('start_date', 'desc')
            ->first();
    }

    /**
     * Get the PeriodicEvent connected to the controller
     * and belonging to the given semester
     * (by default the current one).
     *
     * @return PeriodicEvent|null
     */
    final public function periodicEventForSemester(?Semester $semester): ?PeriodicEvent
    {
        if (is_null($semester)) {
            $semester = Semester::current();
        }
        return PeriodicEvent::where('event_model', $this->periodicEventName)
            ->where('semester_id', $semester->id)
            ->first();
    }

    /**
     * Create or update the current PeriodicEvent connected to the model.
     * Make sure the $data is properly validated:
     * @param Semester $semester
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param Carbon|null $extended_end_date
     * @return PeriodicEvent
     */
    final public function updatePeriodicEvent(Semester $semester, Carbon $start_date, Carbon $end_date, Carbon $extended_end_date = null): PeriodicEvent
    {
        if($end_date < now()) {
            throw new \InvalidArgumentException('End date must be in the future.');
        }
        if($end_date < $start_date) {
            throw new \InvalidArgumentException('End date must be after the start date.');
        }
        if($extended_end_date && $extended_end_date < $end_date) {
            throw new \InvalidArgumentException('Extended end date must be after the end date.');
        }

        return DB::transaction(function () use ($semester, $start_date, $end_date, $extended_end_date) {
            $event = $this->periodicEvent() ?? new PeriodicEvent(['event_model' => $this->periodicEventName]);
            $event->semester_id = $semester->id;
            $event->start_date = $start_date;
            $event->end_date = $end_date;
            $event->extended_end_date = $extended_end_date;
            if($start_date->isFuture()) {
                $event->start_handled = null;
            }
            if($end_date->isFuture()) {
                $event->end_handled = null;
            }
            $event->save();
            $event->refresh();
            return $event;
        });

    }

    /**
     * Check if the PeriodicEvent is currently active or not.
     * start date <= now <= (extended) end date
     * @return bool
     */
    final public function isActive(): bool
    {
        return $this->periodicEvent()?->isActive() ?? false;
    }

    /**
     * @return bool if the end date has been extended or not
     */
    final public function isExtended(): bool
    {
        return $this->periodicEvent()?->isExtended() ?? false;
    }

    /**
     * @return Carbon|null the start date of the current PeriodicEvent
     */
    final public function getStartDate(): ?Carbon
    {
        return $this->periodicEvent()?->startDate();
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    final public function getEndDate(): ?Carbon
    {
        return $this->periodicEvent()?->endDate();
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    final public function getDeadline(): ?Carbon
    {
        return $this->getEndDate();
    }

    /**
     * @return Semester|BelongsTo|null the semester connected to the current PeriodicEvent
     */
    final public function semester(): Semester|BelongsTo|null
    {
        return $this->periodicEvent()?->semester;
    }
}
