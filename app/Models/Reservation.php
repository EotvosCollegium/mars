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
}
