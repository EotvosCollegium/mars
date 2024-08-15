<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Reservation;

use App\Exceptions\ConflictException;

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
     * @var array<int, string>
     */
    protected $fillable = [
        'group_item',
        'user_id',
        'frequency',
        'group_title',
        'group_from',
        'group_until',
        'group_note',
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
    public function groupItem(): BelongsTo
    {
        return $this->belongsTo(ReservableItem::class, 'group_item');
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
        $groupFrom = Carbon::make($this->group_from);
        $groupUntil = Carbon::make($this->group_until);
        $defaultDuration = $groupFrom->diffInMinutes($groupUntil);

        $currentStart = $firstDay;
        $currentStart->hour = $groupFrom->hour;
        $currentStart->minute = $groupFrom->minute;

        $lastDay->hour = $groupFrom->hour;
        $lastDay->minute = $groupFrom->minute;

        DB::transaction(function () use ($currentStart, $lastDay, $defaultDuration) {
            while ($currentStart <= $lastDay) {
                $currentEnd = $currentStart->copy()->addMinutes($defaultDuration);
                if (!$this->groupItem->reservationsInSlot(
                    CarbonImmutable::make($currentStart),
                    CarbonImmutable::make($currentEnd)
                )->isEmpty()) {
                    throw new ConflictException("conflict on $currentStart");
                } else {
                    Reservation::create([
                        'reservable_item_id' => $this->group_item,
                        'user_id' => $this->user_id,
                        'group_id' => $this->id,
                        'verified' => $this->verified,
                        'title' => $this->group_title,
                        'note' => $this->group_note,
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
        $this->reserveInInterval($firstDay, Carbon::make($this->last_day));
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
            $groupFrom = Carbon::make($this->group_from);
            $newLastDay->hour = $groupFrom->hour;
            $newLastDay->minute = $groupFrom->minute;
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
     *
     * If something is null, it does not get changed.
     * If group_from or group_until changes,
     * a ConflictException is thrown if there would be a conflict;
     * then, nothing is done to the database.
     * The reservation must belong to the group;
     * $groupFrom and $groupUntil must be both null or neither.
     * (Otherwise, an InvalidArgumentException is thrown.)
     */
    public function setForAllAfter(
        Reservation $firstReservation,
        ?ReservableItem $groupItem = null,
        ?User $user = null,
        ?string $groupTitle = null,
        ?Carbon $groupFrom = null,
        ?Carbon $groupUntil = null,
        ?string $groupNote = null,
        ?bool $verified = null
    ) {
        if ($firstReservation->group_id != $this->id) {
            throw new \InvalidArgumentException(
                'reservation does not belong to the group'
            );
        }

        // if either one gets changed, we have to do the same things
        if (is_null($groupFrom) && !is_null($groupUntil)) {
            $groupFrom = Carbon::make($this->group_from);
        } elseif (!is_null($groupFrom) && is_null($groupUntil)) {
            $groupUntil = Carbon::make($this->group_until);
        }

        DB::transaction(function () use (
            $firstReservation,
            $groupItem,
            $user,
            $groupTitle,
            $groupFrom,
            $groupUntil,
            $groupNote,
            $verified
        ) {
            $allAfter = $this->reservations()
                ->where('reserved_from', '>=', $firstReservation->reserved_from)
                ->get();
            foreach($allAfter as $reservation) {
                // the item has to be set now;
                // we are going to use it
                if (!is_null($groupItem)) {
                    // now, it will change the item even if it has been custom
                    $reservation->reservable_item_id = $groupItem->id;
                    $reservation->save();
                    $reservation->refresh();
                }

                if (!is_null($groupFrom)) {
                    $newFrom = Carbon::make($reservation->reserved_from);
                    $newFrom->hour = $groupFrom->hour;
                    $newFrom->minute = $groupFrom->minute;
                    $newUntil = Carbon::make($reservation->reserved_until);
                    $newUntil->hour = $groupUntil->hour;
                    $newUntil->minute = $groupUntil->minute;

                    $others = $reservation->reservableItem
                        ->reservationsInSlot(
                            CarbonImmutable::make($newFrom),
                            CarbonImmutable::make($newUntil)
                        )->filter(function (Reservation $other) use ($reservation) {
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
                if (!is_null($groupTitle) && $this->group_title == $reservation->title) {
                    // if it has been custom, it won't be changed
                    $reservation->title = $groupTitle;
                }
                if (!is_null($groupNote) && $this->group_note == $reservation->note) {
                    // same here
                    $reservation->note = $groupNote;
                }
                if (!is_null($verified)) {
                    $reservation->verified = $verified;
                }

                $reservation->save();
            }

            if (isset($groupItem)) {
                $this->group_item = $groupItem->id;
            }
            if (isset($user)) {
                $this->user_id = $user->id;
            }
            if (isset($groupTitle)) {
                $this->group_title = $groupTitle;
            }
            if (isset($groupNote)) {
                $this->group_note = $groupNote;
            }
            if (isset($verified)) {
                $this->verified = $verified;
            }
            $this->save();
        });
    }

    /**
     * Edit the default parameters and set them for all reservations too.
     * Actually calls setForAllAfter with $this->firstReservation().
     *
     * If something is null, it does not get changed.
     * If group_from or group_until changes,
     * a ConflictException is thrown if there would be a conflict;
     * then, nothing is done to the database.
     *  $groupFrom and $groupUntil must be both null or neither.
     * (Otherwise, an InvalidArgumentException is thrown.)
     */
    public function setForAll(
        ?ReservableItem $groupItem = null,
        ?User $user = null,
        ?string $groupTitle = null,
        ?Carbon $groupFrom = null,
        ?Carbon $groupUntil = null,
        ?string $groupNote = null,
        ?bool $verified = null
    ) {
        $this->setForAllAfter(
            $this->firstReservation(),
            $groupItem,
            $user,
            $groupTitle,
            $groupFrom,
            $groupUntil,
            $groupNote,
            $verified
        );
    }
}
