<?php

namespace App\Models\Reservations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

use Carbon\Carbon;
use Carbon\CarbonImmutable;

use App\Enums\ReservableItemType;
use App\Models\User;

// ReservableItem -> reservable_items
class ReservableItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'out_of_order'
    ];

    public const MAX_WASHING_RESERVATIONS = 6;

    /**
     * @return HasMany The reservations that have been made for this particular item.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * @return BelongsToMany The users who have ever made a reservation for this item.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, Reservation::class, 'reservable_item_id', 'user_id');
    }

    /**
     * The users who currently have a reservation for this item.
     */
    public function usersWithActiveReservation()
    {
        return User::whereHas('reservations', function ($query) {
            $query->where('reservable_item_id', $this->id)
                ->where('reserved_until', '>', Carbon::now());
        });
    }

    /**
     * @return bool Returns whether the item is currently out of order.
     */
    public function isOutOfOrder(): bool
    {
        return $this->out_of_order;
    }

    /**
     * @return bool Returns whether the room is free at the moment; or false if out of order.
     */
    public function isFree(): bool
    {
        if ($this->isOutOfOrder()) {
            return false;
        } else {
            $now = Carbon::now();
            return Reservation::where('reservable_item_id', $this->id)
                         ->where('reserved_from', '<=', $now)
                         ->where('reserved_until', '>', $now)
                         ->doesntExist();
        }
    }

    /**
     * Returns whether this is a washing machine.
     */
    public function isWashingMachine(): bool
    {
        return ReservableItemType::WASHING_MACHINE->value == $this->type;
    }

    /**
     * Returns whether this is a room.
     */
    public function isRoom(): bool
    {
        return ReservableItemType::ROOM->value == $this->type;
    }

    /**
     * Returns a query of reservations in a given time interval
     * (those that do not only touch it with their endpoints).
     */
    public function reservationsInSlot(CarbonImmutable $from, CarbonImmutable $until)
    {
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
     * The number of non-expired reservations of a user for the item.
     * Used when inspecting whether someone has too many reservations
     * for a washing machine.
     */
    public function numberOfValidReservations(User $user): int
    {
        if (!$this->isWashingMachine()) {
            throw new \Exception('only for use with washing machines');
        }
        return $this->reservations()
            ->where('user_id', $user->id)
            ->where('reserved_until', '>', Carbon::now())
            ->count();
    }
}
