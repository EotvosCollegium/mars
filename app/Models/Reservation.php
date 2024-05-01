<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservable_item_id',
        'user_id',
        'title',
        'note',
        'reserved_from',
        'reserved_until'
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
     * @return bool Returns whether this reservation conflicts with the one given,
     * if it is not itself.
     */
    public function conflictsWith(Reservation $that): bool
    {
        if ($this == $that
                || $this->reservable_item_id != $that->reservable_item_id) {
            return false;
        } else if ($this->reserved_from < $that->reserved_from) {
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
        if ($reservation->reservableItem->type == 'washing_machine') {
            if (!is_null($reservation->user)) {
                return $reservation->user->name;
            }
        } else {
            if (!is_null($reservation->title)) {
                return $reservation->title;
            }
        }
    }
}
