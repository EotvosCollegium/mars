<?php

namespace App\Models;

use App\Utils\HasPeriodicEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A PeriodicEvent is connected to a feature that is active for a certain period of time.
 * It is connected to the user of the HasPeriodicEvent trait with the `event_model` attribute.
 * @see HasPeriodicEvent
 *
 * @property int $id
 * @property string $event_model
 * @property int|null $semester_id
 * @property \Illuminate\Support\Carbon $start_date
 * @property string|null $start_handled
 * @property \Illuminate\Support\Carbon $end_date
 * @property \Illuminate\Support\Carbon|null $extended_end_date
 * @property string|null $end_handled
 * @property string|null $show_until
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
    protected $fillable = [
        'event_model',
        'start_date',
        'start_handled',
        'end_date',
        'extended_end_date',
        'end_handled',
        'show_until',
        'semester_id'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'extended_end_date' => 'datetime'
    ];

    final public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * @return Carbon|null the start date of the current PeriodicEvent
     */
    final public function startDate(): ?Carbon
    {
        return Carbon::parse($this->start_date);
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    final public function endDate(): ?Carbon
    {
        return Carbon::parse($this->extended_end_date ?? $this->end_date);
    }

    /**
     * @return Carbon|null the end date of the current PeriodicEvent
     */
    final public function deadline(): ?Carbon
    {
        return $this->endDate();
    }

    /**
     * Check if the PeriodicEvent is currently active or not.
     * start date <= now <= (extended) end date
     * @return bool
     */
    final public function isActive(): bool
    {
        if($this->startDate()->isFuture()) {
            return false;
        }
        if($this->endDate()->isPast()) {
            return false;
        }
        return true;
    }

    /**
     * @return bool if the end date has been extended or not
     */
    final public function isExtended(): bool
    {
        return $this->extended_end_date != null;
    }
}
