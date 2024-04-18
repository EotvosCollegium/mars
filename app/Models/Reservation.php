<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
        // Beware: these are strings!
        $from1 = strtotime($this->reserved_from);
        $until1 = strtotime($this->reserved_until);
        $from2 = strtotime($that->reserved_from);
        $until2 = strtotime($that->reserved_until);
        return
            $this->id != $that-> id &&
            $this->reservable_item_id == $that->reservable_item_id &&
            ($from1 <= $from2 && $until1 > $from2 ||
              $from1 < $until2 && $from1 >= $from2);
    }
}