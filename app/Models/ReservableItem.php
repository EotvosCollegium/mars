<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

use Carbon\Carbon;

use App\Models\Reservation;

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
        'allowed_starting_minutes'
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
    /**
     * Returns a collection of reservations in a given time interval
     * (those that do not touch it only with their endpoints).
     */
    public function reservationsInSlot(Carbon $from, Carbon $until): Collection {
        return Reservation::where('reservable_item_id', $this->id)
                          ->where(function (Builder $query) use ($from, $until) {
                            return $query->where(function (Builder $query) use ($from, $until) {
                                           return $query->where('reserved_from', '>=', $from)
                                                        ->where('reserved_from', '<', $until);
                                         })
                                         ->orWhere(function (Builder $query) use ($from) {
                                            return $query->where('reserved_from', '<=', $from)
                                                         ->where('reserved_until', '>', $from);
                                         });
                          })->orderBy('reserved_from')
                          ->get();
    }

    /**
     * Returns whether the room is free
     * in the given time interval.
     * If $until is null, $from will be a single point in time.
     */
    public function isFree(Carbon $from, Carbon $until = null): bool
    {
        if (is_null($until)) $until = $from;
        return $this->reservationsInSlot($from, $until)->empty();
    }
}
