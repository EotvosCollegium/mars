<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Carbon\Carbon;

class ReservableItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'default_reservation_duration',
        'is_default_compulsory',
        'allowed_starting_minutes',
        'out_of_order_from',
        'out_of_order_until'
    ];

    /**
     * @return HasMany The reservations made for this particular item.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * @return BelongsToMany Those who have ever had a reservation for the item.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, Reservation::class, 'reservable_item_id', 'user_id');
    }

    /**
     * Creates a reservation for the given user and parameters, and returns it.
     * @return Reservation The reservation created.
     */
    public function reserve(User $user, ?string $title, ?string $note, Carbon $from, Carbon $until, bool $verified = true): Reservation
    {
        return Reservation::create([
            "reservable_item_id" => $this->id,
            "verified" => $verified,
            "user_id" => $user->id,
            "title" => $title,
            "note" => $note,
            "reserved_from" => $from,
            "reserved_until" => $until
        ]);
    }
}
