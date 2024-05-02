<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Carbon\Carbon;

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
     * Returns whether two time intervals have an intersection.
     * (This does not include when only an end point is common.)
     * @return bool
     */
    public static function haveIntersection(Carbon $from1, Carbon $until1,
                                       Carbon $from2, Carbon $until2): bool {
        return ($from1 <= $from2 && $from2 < $until1)
          || ($from2 <= $from1 && $from1 < $until2);
    }

    /**
     * Whether the reservation conflicts with the other one
     * (they are for the same item and the intervals have an intersection).
     * Returns false if the two are the same.
     * @return bool
     */
    public function conflictsWith(Reservation $that): bool
    {
        return ($this != $that) &&
          Reservation::haveIntersection($this->reserved_from, $this->reserved_until,
                           $that->reserved_from, $that->reserved_until);
    }

    /**
     * Returns how long the intersection of the reservation
     * with the given time slot is
     * (in minutes).
     * Returns 0 if there is no intersection.
     * @return int
     */
    public function lengthOfIntersectionWith(Carbon $from, Carbon $until): int
    {
        $beginning = $from->max($this->reserved_from);
        $end = $until->min($this->reserved_until);
        $diff = $beginning->diffInMinutes($end);
        return ($diff >= 0) ? $diff : 0;
    }

    /**
     * The name to be displayed in the timetable.
     * If the reservation has a name, than that;
     * if not, then the name of the owner;
     * if not even that, an empty string.
     * @return string
     */
    public function displayName(): string
    {
        if (!is_null($this->name)) return $this->name;
        else if (!is_null($this->user)) {
            return $this->user->name;
        } else return "";
    }
}