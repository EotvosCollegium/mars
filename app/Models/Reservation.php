<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reserved_item_id',
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
     * @return BelongsTo The user who has made the reservation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Whether the reservation conflicts with the other one
     * (they are for the same item and the intervals have an intersection).
     * Returns false if the two are the same.
     * @return bool
     */
    public function conflictsWith(Reservation $that): bool
    {
        return
            $this->id != $that-> id &&
            $this->reservable_item_id == $that->reservable_item_id &&
            ($this->reserved_from <= $that->reserved_from && $this->reserved_until > $that->reserved_from ||
             $this->reserved_from < $that->reserved_until && $this->reserved_from >= $that->reserved_from);
    }
}
