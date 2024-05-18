<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Reservation;
use App\Models\ReservableItem;
use App\Models\User;

/** Thrown in transactions when there is a conflict somewhere. */
class ConflictException extends Exception {}

/**
 * Groups recurring reservations into one single item
 * which is easier to manage.
 */
class ReservationGroup extends Model
{
    use HasFactory;

    /** The reservations belonging to this group. */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'group_id');
    }

    /**
     * The item to be used by default
     * if the recurring reservation is extended.
     */
    public function defaultItem(): BelongsTo
    {
        return $this->belongsTo(ReservableItem::class, 'default_item');
    }

    /**
     * The user to whom the group belongs.
     */
    public function user(): User
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Creates the reservations of the group.
     * Should be run after creation.
     * The first one is always the one beginning on the first day;
     * the last one is the one beginning on the 'last' day
     * (or the one before if there is none on that date).
     * If there is a conflict, it returns false
     * and does nothing to the database.
     */
    public function createReservations(): bool
    {
        $currentDate = Carbon::make($this->first_day);
        $endDate = Carbon::make($this->last_day);

        $defaultFrom = Carbon::make($this->default_from);
        $defaultUntil = Carbon::make($this->default_until);
        $defaultDuration = $defaultFrom->diffInMinutes($defaultUntil);

        $currentDate->addHours($defaultFrom->hour);
        $currentDate->addMinutes($defaultFrom->minute);

        try { DB::transaction(function () {
            while ($currentDate <= $endDate) {
                $until = $currentDate->copy()->addMinutes($defaultDuration);
                if (!$this->defaultItem->isFree($currentDate, $until)) {
                    throw new ConflictException();
                } else {
                    Reservation::create([
                        'reservable_item_id' => $this->default_item,
                        'user_id' => $this->user_id,
                        'group_id' => $this->id,
                        'verified' => $this->verified,
                        'title' => $this->title,
                        'note' => $this->default_note,
                        'reserved_from' => $currentDate,
                        'reserved_until' => $until
                    ]);
                    $currentDate->addDays($this->frequency);
                }
            }
        }); return true;
        } catch (ConflictException) {
            return false;
        }
    }

    /**
     * Sets the end date
     * and creates or deletes member reservations accordingly.
     * Deletes the group and returns false
     * if there is no reservation left in it.
     */
    public function setLastDay(Carbon $newLastDay)
    {
        $oldLastDay = Carbon::make($this->last_day);

    }
}
