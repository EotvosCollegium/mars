<?php

namespace App\Models;

use App\Events\SemesterEvaluationPeriodEnd;
use App\Events\SemesterEvaluationPeriodReminder;
use App\Events\SemesterEvaluationPeriodStart;
use App\Http\Controllers\Auth\ApplicationController;
use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Http\Controllers\StudentsCouncil\MrAndMissController;
use App\Jobs\PeriodicEventsProcessor;
use App\Mail\EvaluationFormAvailable;
use App\Mail\EvaluationFormAvailableDetails;
use App\Utils\PeriodicEventController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * A PeriodicEvent is connected to a feature that is active for a certain period of time.
 * It is connected to the `$periodicEventName` defined in PeriodicEventController, stored as the `event_model` attribute.
 * @warning PeriodicEvents should only be modified by a PeriodicEventController.
 * @warning Do not attach other models to PeriodicEvents, use the connected Semester ids instead.
 * @see PeriodicEventController
 * @see PeriodicEventsProcessor
 *
 * @property int $id
 * @property string $event_model
 * @property int|null $semester_id
 * @property \Illuminate\Support\Carbon $start_date
 * @property string|null $start_handled
 * @property \Illuminate\Support\Carbon $end_date
 * @property \Illuminate\Support\Carbon|null $extended_end_date
 * @property string|null $end_handled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Semester|null $semester
 * @method static Builder|PeriodicEvent newModelQuery()
 * @method static Builder|PeriodicEvent newQuery()
 * @method static Builder|PeriodicEvent query()
 * @method static Builder|PeriodicEvent whereCreatedAt($value)
 * @method static Builder|PeriodicEvent whereEndDate($value)
 * @method static Builder|PeriodicEvent whereEndHandled($value)
 * @method static Builder|PeriodicEvent whereEventModel($value)
 * @method static Builder|PeriodicEvent whereExtendedEndDate($value)
 * @method static Builder|PeriodicEvent whereId($value)
 * @method static Builder|PeriodicEvent whereSemesterId($value)
 * @method static Builder|PeriodicEvent whereShowUntil($value)
 * @method static Builder|PeriodicEvent whereStartDate($value)
 * @method static Builder|PeriodicEvent whereStartHandled($value)
 * @method static Builder|PeriodicEvent whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PeriodicEvent extends Model
{
    public const SEMESTER_EVALUATION_PERIOD = "SEMESTER_EVALUATION_PERIOD";
    public const APPLICATION_PERIOD = "APPLICATION_PERIOD";
    public const KKT_NETREG_PAYMENT_PERIOD = "KKT_NETREG_PAYMENT_PERIOD";
    public const MR_AND_MISS_VOTING_PERIOD = "MR_AND_MISS_VOTING_PERIOD";

    protected $fillable = [
        'event_model',
        'start_date',
        'start_handled',
        'end_date',
        'extended_end_date',
        'end_handled',
        'semester_id'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'extended_end_date' => 'datetime'
    ];

    /**
     * @return BelongsTo the Semester that the PeriodicEvent is connected to
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * @return Carbon|null the start date of the current PeriodicEvent
     */
    public function startDate(): ?Carbon
    {
        return Carbon::parse($this->start_date);
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    public function endDate(): ?Carbon
    {
        return Carbon::parse($this->extended_end_date ?? $this->end_date);
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    public function deadline(): ?Carbon
    {
        return $this->endDate();
    }

    /**
     * Check if the PeriodicEvent is currently active or not.
     * start date <= now <= (extended) end date
     * @return bool
     */
    public function isActive(): bool
    {
        return !$this->startDate()->isFuture() && !$this->endDate()->isPast();
    }

    /**
     * @return bool if the end date has been extended or not
     */
    public function isExtended(): bool
    {
        return $this->extended_end_date != null;
    }


    /**
     * Handle the start of the PeriodicEvent.
     */
    public function handleStart(): void
    {
        switch ($this->event_model) {
            case self::SEMESTER_EVALUATION_PERIOD:
                event(new SemesterEvaluationPeriodStart($this));
                break;
            default:
                Log::debug("No event handler defined for ". $this->event_model . "'s start time");
        }

        $this->start_handled = now();
        $this->save(['timestamps' => false]); // save without updating timestamps
    }

    /**
     * Handle the end of the PeriodicEvent.
     */
    public function handleEnd(): void
    {
        switch ($this->event_model) {
            case self::SEMESTER_EVALUATION_PERIOD:
                event(new SemesterEvaluationPeriodEnd($this));
                break;
            default:
                Log::debug("No event handler defined for ". $this->event_model . "'s end time");
        }

        $this->end_handled = now();
        $this->save(['timestamps' => false]); // save without updating timestamps
    }

    /**
     * Handle the end of the PeriodicEvent.
     */
    public function handleReminder(): void
    {
        switch ($this->event_model) {
            case self::SEMESTER_EVALUATION_PERIOD:
                event(new SemesterEvaluationPeriodReminder($this));
                break;
            default:
                Log::debug("No event handler defined for ". $this->event_model . "'s reminder");
        }
    }


}
