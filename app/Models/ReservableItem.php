<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Whether the item is out of order.
     * We assume $this->out_of_order_from and $this->out_of_order_until
     * are valid time strings.
     * @return bool
     */
    public function isOutOfOrder(): bool
    {
        if (is_null($this->out_of_order_from)) return false;
        else {
            $from = strtotime($this->out_of_order_from);
            $now = time();
            if ($now < $from) return false;
            else if (is_null($this->out_of_order_until)) return true;
            else return $now < strtotime($this->out_of_order_until);
        }
    }

    /**
     * Whether the item is free at the given time
     * (given with a Carbon object).
     * The default is Carbon::now().
     * Returns false if out of order.
     * @return bool
     */
    public function isFree(Carbon $time = null): bool {
        if (is_null($time)) $time = Carbon::now();
        if ($this->isOutOfOrder()) return false;
        else {
            return Reservation::where('reservable_item_id', $this->id)
                                ->where('reserved_from', '<=', $time)
                                ->where('reserved_until', '>', $time)
                                ->doesntExist();
        }
    }
}
