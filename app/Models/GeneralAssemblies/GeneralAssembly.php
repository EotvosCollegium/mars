<?php

namespace App\Models\GeneralAssemblies;

use App\Enums\PresenceType;
use App\Models\EducationalInformation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * App\Models\GeneralAssemblies\GeneralAssembly
 *
 * @property int $id
 * @property string $title
 * @property \Illuminate\Support\Carbon|null $opened_at
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $excusedUsers
 * @property-read int|null $excused_users_count
 * @property-read int $presence_checks_needed
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\GeneralAssemblies\PresenceCheck[] $presenceChecks
 * @property-read int|null $presence_checks_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Question[] $questions
 * @property-read int|null $questions_count
 * @method static \Database\Factories\GeneralAssemblies\GeneralAssemblyFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralAssembly newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralAssembly newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralAssembly query()
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralAssembly whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralAssembly whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralAssembly whereOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GeneralAssembly whereTitle($value)
 * @mixin \Eloquent
 */
class GeneralAssembly extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['title', 'opened_at', 'closed_at'];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * The questions that belong to the general_assembly.
     * @return MorphMany
     */
    public function questions(): MorphMany
    {
        return $this->morphMany(Question::class, 'parent');
    }

    /**
     * @return HasMany The presences that belong to the general_assembly.
     */
    public function presenceChecks(): HasMany
    {
        return $this->hasMany(PresenceCheck::class);
    }

    /**
     * * @return BelongsToMany The users who are missing with an excuse. Pivot data is included: comment.
     */
    public function excusedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'general_assembly_excused_users')
            ->withPivot('comment');
    }

    /**
     * @return bool Whether the general_assembly has been opened (regardless of whether it has been closed since then).
     */
    public function hasBeenOpened(): bool
    {
        return !empty($this->opened_at) && $this->opened_at <= now();
    }

    /**
     * @return bool Whether the question is currently open.
     */
    public function isOpen(): bool
    {
        return $this->hasBeenOpened() &&
                !$this->isClosed();
    }

    /**
     * @return bool Whether the question has been closed.
     */
    public function isClosed(): bool
    {
        return !empty($this->closed_at) && $this->closed_at <= now();
    }

    /**
     * @return Collection|User[]|array The users who have attended the general assembly.
     * If there was no presence check, returns an empty array.
     */
    public function attendees(): Collection|array
    {
        if ($this->getPresenceChecksNeededAttribute() == 0) {
            return collect([]);
        }
        return User::whereHas(
            'presenceChecks',
            fn (Builder $query) => $query->where('presence_checks.general_assembly_id', $this->id),
            '>=',
            $this->getPresenceChecksNeededAttribute(),
        )->get();
    }

    /**
     * @param User $user The user to check.
     * @return bool Whether the user has attended the general assembly. The user failed a maximum of 2 presence checks.
     */
    public function isAttended(User $user): bool
    {
        return $user
            ->presenceChecks()
            ->where(
                'presence_checks.general_assembly_id',
                $this->id,
            )
            ->count()
            >=
            $this->getPresenceChecksNeededAttribute()
            ||
            $this->excusedUsers()->where('user_id', $user->id)->count() > 0;
    }

    /**
     * @param User $user The user to check.
     * @return bool Whether the user has attended at least one from the last two general assemblies.
     */
    public static function requirementsPassed(User $user): bool
    {
        $year_of_acceptance = $user->educationalInformation->year_of_acceptance;
        $acceptance_date = Carbon::createFromDate($year_of_acceptance, 9, 1);

        $lastAssemblies = GeneralAssembly::orderBy('closed_at', 'desc')->take(2)->get();
        $secondLastAssembly = $lastAssemblies->count() >= 2 ? $lastAssemblies[1] : null;

        if ($secondLastAssembly && $secondLastAssembly->closed_at < $acceptance_date) {
            return true;
        }
        foreach ($lastAssemblies as $assembly) {
            if ($assembly->isAttended($user)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Opens the question.
     */
    public function open(): void
    {
        if ($this->hasBeenOpened()) {
            throw new \Exception("tried to open general assembly when it has already been opened");
        }
        $this->update(['opened_at' => now()]);
    }

    /**
     * Closes the question.
     */
    public function close(): void
    {
        if ($this->isClosed()) {
            throw new \Exception("tried to close general assembly when it has already been closed");
        }
        if (!$this->isOpen()) {
            throw new \Exception("tried to close general assembly when it was not open");
        }
        foreach ($this->questions as $question) {
            if ($question->isOpen()) {
                $question->close();
            }
        }
        foreach($this->presenceChecks as $presenceCheck) {
            if ($presenceCheck->isOpen()) {
                $presenceCheck->close();
            }
        }
        $this->update(['closed_at' => now()]);
    }

    /**
     * @return int The number of presence checks needed for a user to be counted as an attended user.
     */
    public function getPresenceChecksNeededAttribute(): int
    {
        $presenceCheckCount = $this->presenceChecks()->count();
        return $presenceCheckCount <= 2 ? $presenceCheckCount : $presenceCheckCount - 2;
    }

    /**
     * Returns a random 6 char string, refreshed every minute.
     */
    public static function getTemporaryPasscode($offset = "0 minute"): string
    {
        return substr(hash('sha256', date('Y-m-d H:i', strtotime($offset))), 0, 6);
    }

    /**
     * Decides if a value matches the current temporary password.
     * The previous password is also accepted.
     */
    public static function isTemporaryPasscode(string $value): bool
    {
        return $value == self::getTemporaryPasscode()
            || $value == self::getTemporaryPasscode('-1 minute');
    }
}
