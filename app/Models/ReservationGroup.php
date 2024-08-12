<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Reservation;

class ConflictException extends \Exception
{
}

class ReservationGroup extends Model
{
    use HasFactory;

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

    /**
     * The reservations belonging to the group.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'group_id');
    }

    /**
     * The default item to which the group belongs.
     */
    public function defaultItem(): BelongsTo
    {
        return $this->belongsTo(ReservableItem::class, 'default_item');
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
        $defaultDuration = $defaultFrom->diffInMinutes($defaultUntil);

        $currentStart = $firstDay;
        $currentStart->hour = $defaultFrom->hour;
        $currentStart->minute = $defaultFrom->minute;

        $lastDay->hour = $defaultFrom->hour;
        $lastDay->minute = $defaultFrom->minute;

        DB::transaction(function () use ($currentStart, $lastDay, $defaultDuration) {
            while ($currentStart <= $lastDay) {
                $currentEnd = $currentStart->copy()->addMinutes($defaultDuration);
                if (!$this->defaultItem->reservationsInSlot($currentStart, $currentEnd)
                        ->isEmpty()) {
                    throw new ConflictException("conflict on $currentStart");
                } else {
                    Reservation::create([
                        'reservable_item_id' => $this->default_item,
                        'user_id' => $this->user_id,
                        'group_id' => $this->id,
                        'verified' => $this->verified,
                        'title' => $this->default_title,
                        'note' => $this->default_note,
                        'reserved_from' => $currentStart,
                        'reserved_until' => $currentEnd
                    ]);
                    $currentStart->addDays($this->frequency);
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
    public function initializeFrom(Carbon|string $firstDay): void
    {
        if (is_string($firstDay)) {
            $firstDay = Carbon::make($firstDay);
        }
        $this->reserveInInterval($firstDay, $this->last_day);
    }

    /** The earliest reservation belonging to the group. */
    public function firstReservation(): Reservation
    {
        $dateOfFirst = Reservation::where('group_id', $this->id)
                         ->min('reserved_from');
        return Reservation::where('group_id', $this->id)
                         ->where('reserved_from', $dateOfFirst)
                         ->first();
    }

    /** The starting date of the last reservation. */
    public function dateOfLast(): Carbon
    {
        return $this->reservations->map(
            function (Reservation $reservation) {
                return Carbon::make($reservation->reserved_from);
            }
        )->max();
    }

    /**
     * Sets the end date
     * and creates or deletes member reservations accordingly.
     * Throws InvalidArgumentException if $newLastDay is earlier than the first day
     * (so if there would be no reservations left).
     * Throws ConflictException if there would be a conflict;
     * in that case, no change to the database is made.
     */
    public function setLastDay(Carbon|string $newLastDay): void
    {
        if (is_string($newLastDay)) {
            $newLastDay = Carbon::make($newLastDay);
        }
        $oldLastDay = Carbon::make($this->last_day);
        if ($oldLastDay < $newLastDay) {
            $fromDay = $this->dateOfLast()
                ->addDays($this->frequency);
            $this->reserveInInterval($fromDay, $newLastDay);
        } else {
            $defaultFrom = Carbon::make($this->default_from);
            $newLastDay->hour = $defaultFrom->hour;
            $newLastDay->minute = $defaultFrom->minute;
            $this->reservations()
                ->where('reserved_from', '>', $newLastDay)
                ->delete();
        }
        $this->last_day = $newLastDay;
        $this->save();
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
        Reservation $firstReservation,
        ?ReservableItem $defaultItem = null,
        ?User $user = null,
        ?string $defaultTitle = null,
        ?Carbon $defaultFrom = null,
        ?Carbon $defaultUntil = null,
        ?string $defaultNote = null,
        ?bool $verified = null
    ) {
        if ($firstReservation->group_id != $this->id) {
            throw new InvalidArgumentException(
                'reservation does not belong to the group'
            );
        }

        // if either one gets changed, we have to do the same things
        if (is_null($defaultFrom) && !is_null($defaultUntil)) {
            $defaultFrom = Carbon::make($this->default_from);
        } elseif (!is_null($defaultFrom) && is_null($defaultUntil)) {
            $defaultUntil = Carbon::make($this->default_until);
        }

        DB::transaction(function () use (
            $firstReservation,
            $defaultItem,
            $user,
            $defaultTitle,
            $defaultFrom,
            $defaultUntil,
            $defaultNote,
            $verified
        ) {
            $allAfter = $this->reservations()
                ->where('reserved_from', '>=', $firstReservation->reserved_from)
                ->get();
            foreach($allAfter as $reservation) {
                // the item has to be set now;
                // we are going to use it
                if (!is_null($defaultItem)) {
                    // now, it will change the item even if it has been custom
                    $reservation->reservable_item_id = $defaultItem->id;
                    $reservation->save();
                    $reservation->refresh();
                }

                if (!is_null($defaultFrom)) {
                    $newFrom = Carbon::make($reservation->reserved_from);
                    $newFrom->hour = $defaultFrom->hour;
                    $newFrom->minute = $defaultFrom->minute;
                    $newUntil = Carbon::make($reservation->reserved_until);
                    $newUntil->hour = $defaultUntil->hour;
                    $newUntil->minute = $defaultUntil->minute;

                    $others = $reservation->reservableItem
                        ->reservationsInSlot($newFrom, $newUntil)
                        ->filter(function (Reservation $other) use ($reservation) {
                            return $other->id != $reservation->id;
                        });
                    if (!$others->isEmpty()) {
                        throw new ConflictException(
                            "conflict on $newFrom"
                        );
                    }

                    $reservation->reserved_from = $newFrom;
                    $reservation->reserved_until = $newUntil;
                }

                if (!is_null($user)) {
                    $reservation->user_id = $user->id;
                }
                if (!is_null($defaultTitle) && $this->default_title == $reservation->title) {
                    // if it has been custom, it won't be changed
                    $reservation->title = $defaultTitle;
                }
                if (!is_null($defaultNote) && $this->default_note == $reservation->note) {
                    // same here
                    $reservation->note = $defaultNote;
                }
                if (!is_null($verified)) {
                    $reservation->verified = $verified;
                }

                $reservation->save();
            }

            if (isset($defaultItem)) {
                $this->default_item = $defaultItem->id;
            }
            if (isset($user)) {
                $this->user_id = $user->id;
            }
            if (isset($defaultTitle)) {
                $this->default_title = $defaultTitle;
            }
            if (isset($defaultNote)) {
                $this->default_note = $defaultNote;
            }
            if (isset($verified)) {
                $this->verified = $verified;
            }
            $this->save();
        });
    }

    public function setForAll(
        ?ReservableItem $defaultItem = null,
        ?User $user = null,
        ?string $defaultTitle = null,
        ?Carbon $defaultFrom = null,
        ?Carbon $defaultUntil = null,
        ?string $defaultNote = null,
        ?bool $verified = null
    ) {
        $this->setForAllAfter(
            $this->firstReservation(),
            $defaultItem,
            $user,
            $defaultTitle,
            $defaultFrom,
            $defaultUntil,
            $defaultNote,
            $verified
        );
    }
}
