<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

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
     * Whether the item is out of order at the given time.
     * (given with a Carbon object).
     * The default is Carbon::now().
     * @return bool
     */
    public function isOutOfOrder(Carbon $time = null): bool
    {
        if (is_null($time)) $time = Carbon::now();
        if (is_null($this->out_of_order_from)) return false;
        else {
            if ($time < $this->out_of_order_from) return false;
            else if (is_null($this->out_of_order_until)) return true;
            else return $time < $this->out_of_order_until;
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

    /**
     * Returns which reservation belongs to a given slot, if any.
     * If there are more than one reservations in the slot,
     * this returns the one that has the largest intersection
     * with the slot.
     * @return Reservation|null
     */
    public function reservationForSlot(Carbon $from, Carbon $until): Reservation|null {
        // first select the ones that do have an intersection
        $reservations =
          Reservation::where('reservable_item_id', $this->id)
                     ->where(function (Builder $query) use ($from, $until) {
                       $query->where(function (Builder $query) use ($from, $until) {
                             $query->where('reserved_from', '>=', $from)
                                   ->where('reserved_from', '<', $until);
                       })->orWhere(function (Builder $query) use ($from) {
                             $query->where('reserved_from', '<=', $from)
                                   ->where('reserved_until', '>', $from);
                     });})
                     ->get();
        $toReturn = null;
        $maxIntersection = null;
        foreach($reservations as $reservation) {
            $intersection = $reservation->lengthOfIntersectionWith($from, $until);
            if (is_null($toReturn)
                  || $maxIntersection < $intersection) {
                $toReturn = $reservation;
                $maxIntersection = $intersection;
            }
        }
        return $toReturn;
    }

    /** These are returned by statusOfSlot. */
    // public const OCCUPIED = 'occupied';
    public const FREE = 'free';
    public const OUT_OF_ORDER = 'out_of_order';

    /**
     * Returns the "status" of a slot
     * (occupied, free or out of order).
     * This will determine the colour of the slot.
     * If there is a reservation, this returns it.
     * @return string|Reservation
     */
    public function statusOfSlot(Carbon $from, Carbon $until): string|Reservation {
        $reservation = $this->reservationForSlot($from, $until);
        if (!is_null($reservation)) return $reservation;
        else if (is_null($this->out_of_order_from) || $until <= $this->out_of_order_from) {
            return ReservableItem::FREE;
        }
        else if (is_null($this->out_of_order_until) || $this->out_of_order_until > $from) {
            return ReservableItem::OUT_OF_ORDER;
        }
        else return ReservableItem::FREE;
    }
}
