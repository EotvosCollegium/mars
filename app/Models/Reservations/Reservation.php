<?php

namespace App\Models\Reservations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\User;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservable_item_id',
        'user_id',
        'group_id',
        'title',
        'note',
        'reserved_from',
        'reserved_until',
        'verified'
    ];

    /**
     * @return BelongsTo The item reserved.
     */
    public function reservableItem(): BelongsTo
    {
        return $this->belongsTo(ReservableItem::class);
    }

    /**
     * @return BelongsTo The user to whom the reservation belongs.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The group to which the reservation belongs, if any.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ReservationGroup::class, 'group_id');
    }

    /**
     * Whether the reservation belongs to a group of recurring events.
     */
    public function isRecurring(): bool
    {
        return isset($this->group_id);
    }

    /**
     * @return bool Returns whether this reservation conflicts with the one given,
     * if it is not itself.
     */
    public function conflictsWith(Reservation $that): bool
    {
        if ($this == $that
                || $this->reservable_item_id != $that->reservable_item_id) {
            return false;
        } elseif ($this->reserved_from < $that->reserved_from) {
            return $this->reserved_until > $that->reserved_from;
        } else {
            return $this->reserved_from < $that->reserved_until;
        }
    }

    /**
     * A name to be displayed in a timetable.
     */
    public function displayName(): string
    {
        if ($this->reservableItem->isWashingMachine()) {
            return $this->user->name;
        } else {
            return (
                isset($this->title)
                ? $this->title
                : $this->user->name
            ) . (
                $this->isRecurring()
                ? " ({$this->group->frequency}" . __('reservations.frequency_comment') . ")"
                : ""
            ) . (
                $this->verified
                ? ""
                : (" (" . __('reservations.unverified'). ")")
            );
        }
    }
}
