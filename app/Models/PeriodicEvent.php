<?php

namespace App\Models;

use App\Jobs\PeriodicEventsProcessor;
use App\Utils\HasPeriodicEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A PeriodicEvent is connected to a feature that is active for a certain period of time.
 * It is connected to the user of the HasPeriodicEvent trait with the `event_model` attribute.
 * @warning PeriodicEvents should only be modified by the HasPeriodicEvent trait.
 * @warning Do not attach other models to PeriodicEvents, use the connected Semester ids instead.
 * @see HasPeriodicEvent
 * @see PeriodicEventsProcessor
 *
 * @property int $id
 * @property string $event_model
 * @property int|null $semester_id
 * @property Carbon $start_date
 * @property string|null $start_handled
 * @property Carbon $end_date
 * @property Carbon|null $extended_end_date
 * @property string|null $end_handled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
        //Get the corresponding controller and call its start method
        app($this->event_model)->handlePeriodicEventStart();

        $this->start_handled = now();
        $this->save(['timestamps' => false]); // save without updating timestamps
    }

    /**
     * Handle the end of the PeriodicEvent.
     */
    public function handleEnd(): void
    {
        //Get the corresponding controller and call its start method
        app($this->event_model)->handlePeriodicEventEnd();

        $this->end_handled = now();
        $this->save(['timestamps' => false]); // save without updating timestamps
    }

    /**
     * Handle the end of the PeriodicEvent.
     */
    public function handleReminder(): void
    {
        $days_left = (int)$this->endDate()->diffInDays(now()) * (-1);

        //Get the corresponding controller and call its start method
        app($this->event_model)->handlePeriodicEventReminder($days_left);
    }


}
