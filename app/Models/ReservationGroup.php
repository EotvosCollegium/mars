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
class ConflictException extends \Exception {}

/**
 * Groups recurring reservations into one single item
 * which is easier to manage.
 */
class ReservationGroup extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'default_item',
        'user_id',
        'frequency',
        'default_title',
        'default_from',
        'default_until',
        'default_note',
        'last_day',
        'verified'
    ];

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
     * Creates reservations of the group
     * in the time span given.
     * The first one is always the one beginning on the first day;
     * the last one is the one beginning on the 'last' day
     * (or the one before if there is none on that date).
     * If there is a conflict, it throws a ConflictException
     * and does nothing to the database.
     */
    private function reserveInInterval(Carbon $firstDay, Carbon $lastDay): void
    {
        $defaultFrom = Carbon::make($this->default_from);
        $defaultUntil = Carbon::make($this->default_until);
        // if it flows through midnight:
        if ($defaultFrom > $defaultUntil) $defaultUntil->addDay();
        $defaultDuration = $defaultFrom->diffInMinutes($defaultUntil);

        $currentDate = $firstDay->copy();
        $currentDate->hour = $defaultFrom->hour;
        $currentDate->minute = $defaultFrom->minute;

        // so that _<=_ works
        $lastDay->hour = $defaultFrom->hour;
        $lastDay->minute = $defaultFrom->minute;

        DB::transaction(function () use ($currentDate, $lastDay, $defaultDuration) {
            while ($currentDate <= $lastDay) {
                $until = $currentDate->copy()->addMinutes($defaultDuration);
                if (!$this->defaultItem->isFree($currentDate, $until)) {
                    throw new ConflictException(
                        "conflict on $currentDate"
                    );
                } else {
                    Reservation::create([
                        'reservable_item_id' => $this->default_item,
                        'user_id' => $this->user_id,
                        'group_id' => $this->id,
                        'verified' => $this->verified,
                        'title' => $this->default_title,
                        'note' => $this->default_note,
                        'reserved_from' => $currentDate,
                        'reserved_until' => $until
                    ]);
                    $currentDate->addDays($this->frequency);
                }
            }
        });
    }

    /**
     * Creates the reservations of the group, from the given date.
     * Should be run after creation.
     * If there is a conflict, it throws a ConflictException
     * and does nothing to the database.
     */
    public function initializeFrom(Carbon|string $from): void
    {
        if (is_string($from)) $from = Carbon::make($from);
        $this->reserveInInterval($from, Carbon::make($this->last_day));
    }

    /** The first reservation belonging to the group. */
    public function firstReservation(): Reservation
    {
        $dateOfFirst = Reservation::where('group_id', $this->id)->min('reserved_from');
        return Reservation::where('group_id', $this->id)->where('reserved_from', $dateOfFirst)
            ->first();
    }

    /** The starting date of the last reservation. */
    public function dateOfLast(): Carbon
    {
        return $this->reservations->map(
            function (Reservation $reservation) {
                return Carbon::make($reservation->reserved_from);
            })->max();
    }

    /**
     * Sets the end date
     * and creates or deletes member reservations accordingly.
     * Throws InvalidArgumentException if $newLastDay is earlier than the first day
     * (so if there would be no reservations left).
     * Throws ConflictException if there would be a conflict;
     * in that case, no change to the database is made.
     */
    public function setLastDay(Carbon $newLastDay): void
    {
        $oldLastDay = Carbon::make($this->last_day);
        if ($oldLastDay < $newLastDay) {
            $this->reserveInInterval(
                $this->dateOfLast()->addDays($this->frequency),
                $newLastDay
            );
            $this->last_day = $newLastDay;
            $this->save();
        } else if ($oldLastDay < Carbon::make($this->firstReservation()->reserved_from)) {
            throw new InvalidArgumentException(
                "new last day would be earlier than the first day"
            );
        } else {
            // TODO: does this get converted correctly?
            $this->reservations()->where('reserved_from', '>', $newLastDay)
                ->delete();
        }
    }

    /**
     * Verifies or unverifies the group and all of its reservations.
     * Saves everything.
     */
    public function verify($verified = true): void
    {
        foreach($this->reservations as $reservation) {
            $reservation->verified = true;
            $reservation->save();
        }
        $this->verified = $verified;
        $this->save();
    }

    /**
     * Edit the default parameters and set them for all reservations too.
     * If something is null, it does not get changed.
     * If default_from or default_until changes,
     * a ConflictException is thrown if there would be a conflict;
     * then, nothing is done to the database.
     */
    public function setForAll(
        ?ReservableItem $defaultItem = null,
        ?User $user = null,
        // ?int $frequency = null, // it cannot be set for now
        ?string $defaultTitle = null,
        ?Carbon $defaultFrom = null,
        ?Carbon $defaultUntil = null,
        ?string $defaultNote = null,
        ?bool $verified = null
    ): void
    {
        // if either one gets changed, we have to do the same things
        if (is_null($defaultFrom) && !is_null($defaultUntil)) {
            $defaultFrom = Carbon::make($this->default_from);
        } else if (!is_null($defaultFrom) && is_null($defaultUntil)) {
            $defaultUntil = Carbon::make($this->default_until);
        }

        DB::transaction(function () use ($defaultItem, $user, $defaultTitle,
                                         $defaultFrom, $defaultUntil, $defaultNote, $verified) {
            foreach ($this->reservations as $reservation) {

                // the item has to be set now;
                // we are going to use it
                if (!is_null($defaultItem)) {
                    // now, it will change the item even if it has been custom
                    $reservation->reservable_item_id = $defaultItem->id;
                    $reservation->save();
                    $reservation->refresh();
                }

                if (!is_null($defaultFrom)) { // and in this case, $defaultUntil is not null either
                    // this is the hard part
                    $newFrom = Carbon::make($reservation->reserved_from);
                    $newFrom->hour = $defaultFrom->hour;
                    $newFrom->minute = $defaultFrom->minute;
                    $newUntil = Carbon::make($reservation->reserved_until);
                    $newUntil->hour = $defaultUntil->hour;
                    $newUntil->minute = $defaultUntil->minute;

                    // check for conflicts
                    // (but we can conflict with ourselves)
                    $others = $reservation->reservableItem
                        ->reservationsInSlot($newFrom, $newUntil)
                        ->filter(function (Reservation $other) use ($reservation) {
                            return $reservation->id != $other->id;
                        });
                    if (!$others->isEmpty()) {
                        throw new ConflictException(
                            "Conflict at $newFrom"
                        );
                    }
                    $reservation->reserved_from = $newFrom;
                    $reservation->reserved_until = $newUntil;
                }

                if (!is_null($user)) $reservation->user_id = $user->id;
                if (!is_null($defaultTitle) && $this->default_title == $reservation->title) {
                    // if it has been custom, it won't be changed
                    $reservation->title = $defaultTitle;
                }
                if (!is_null($defaultNote) && $this->default_note == $reservation->note) {
                    // same here
                    $reservation->note = $defaultNote;
                }
                if (!is_null($verified)) $reservation->verified = $verified;

                $reservation->save();
            }

            if (isset($defaultItem)) $this->default_item = $defaultItem->id;
            if (isset($user)) $this->user_id = $user->id;
            if (isset($defaultTitle)) $this->default_title = $defaultTitle;
            if (isset($defaultNote)) $this->default_note = $defaultNote;
            if (isset($verified)) $this->verified = $verified;
            $this->save();
        });
    }

    /**
     * Detaches all reservations before a given one
     * from the group.
     * The reservation must belong to the group;
     * otherwise, an InvalidArgumentException is thrown.
     */
    public function detachAllBefore(Reservation $reservation): void
    {
        if ($reservation->group_id != $this->id) {
            throw new InvalidArgumentException(
                'the reservation given does not belong to the group'
            );
        }

        foreach(
            $this->reservations()->where('reserved_from', '<', $reservation->reserved_from)
                ->get()
            as $oldReservation
        ) {
            $oldReservation->group_id = null;
            $oldReservation->save();
        }
    }

    /**
     * Edit the default parameters and set them
     * for all reservations after a given one.
     * If something is null, it does not get changed.
     * If default_from or default_until changes,
     * a ConflictException is thrown if there would be a conflict;
     * then, nothing is done to the database.
     * The reservation must belong to the group;
     * $defaultFrom and $defaultUntil must be both null or neither.
     * (Otherwise, an InvalidArgumentException is thrown.)
     */
    public function setForAllAfter(
        Reservation $reservation,
        ?ReservableItem $defaultItem = null,
        ?User $user = null,
        // ?int $frequency = null, // it cannot be set for now
        ?string $defaultTitle = null,
        ?Carbon $defaultFrom = null,
        ?Carbon $defaultUntil = null,
        ?string $defaultNote = null,
        ?bool $verified = null
    ): void
    {
        DB::transaction(function () use ($reservation, $defaultItem, $user, $defaultTitle,
                                         $defaultFrom, $defaultUntil, $defaultNote) {
            // detach all reservations before this one
            $this->detachAllBefore($reservation); // this throws if needed

            // and for the remaining ones:
            $this->setForAll(
                defaultItem: $defaultItem,
                user: $user,
                // frequency: $frequency,
                defaultTitle: $defaultTitle,
                defaultFrom: $defaultFrom,
                defaultUntil: $defaultUntil,
                defaultNote: $defaultNote,
                verified: $verified
            );
        });
    }
}
