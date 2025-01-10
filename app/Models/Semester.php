<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

use App\Models\User;
use App\Models\AnonymousQuestions\AnswerSheet;
use App\Models\Question;

/**
 * A semester is identified by a year and by it's either autumn or spring.
 *
 * ie. a spring semester starting in february 2020 will be (2019, 2) since we write 2019/20/2.
 * The autumn semester starting in september 2020 is (2020, 1) since we write 2020/21/1.
 *
 * The status can be verified or not (by default it is not). Users with permission has to
 * confirm that the user can have the given status.
 *
 * @property mixed $id
 * @property int $year
 * @property mixed $part
 * @property int $verified
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CommunityService[] $communityServices
 * @property-read int|null $community_services_count
 * @property-read string $name
 * @property-read string $tag
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Transaction[] $transactions
 * @property-read int|null $transactions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WorkshopBalance[] $workshopBalances
 * @property-read int|null $workshop_balances_count
 * @method static \Database\Factories\SemesterFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Semester newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Semester newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Semester query()
 * @method static \Illuminate\Database\Eloquent\Builder|Semester whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Semester wherePart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Semester whereVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Semester whereYear($value)
 * @mixin \Eloquent
 */
class Semester extends Model
{
    use HasFactory;

    protected $table = 'semesters';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'year',
        'part',
    ];

    private const SEPARATOR = '/';

    public const PARTS = [1, 2];

    // Values are in month
    // TODO: change to dates?
    public const START_OF_SPRING_SEMESTER = 2;
    public const END_OF_SPRING_SEMESTER = 7;
    public const START_OF_AUTUMN_SEMESTER = 9;
    public const END_OF_AUTUMN_SEMESTER = 2;

    /**
     * Returns the existing semesters until the current one (included).
     */
    public static function allUntilCurrent()
    {
        return Semester::all()->filter(function ($value, $key) {
            return $value->getStartDate() < Carbon::now();
        });
    }

    /**
     * Get the tag attribute. This should be displayed on the UI.
     * Format: YYYY-YYYY-{part} separated by the SEPARATOR constant.
     *
     * @return Attribute
     */
    public function tag(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->year . self::SEPARATOR . ($this->year + 1) . self::SEPARATOR . $this->part
        );
    }

    /**
     * Alias for tag.
     *
     * @return Attribute
     */
    public function name(): Attribute
    {
        return $this->tag();
    }

    /**
     * Returns a semester by a tag (eg. 2020-2021-2).
     */
    public static function byTag(string $tag): Semester
    {
        $parts = explode(self::SEPARATOR, $tag);

        return self::getOrCreate((int)$parts[0], (int)$parts[2]);
    }

    /**
     * Returns a semester's exact starting and ending dates.
     */
    public function datesToText(): string
    {
        return $this->getStartDate()->format('Y.m.d') . '-' . $this->getEndDate()->format('Y.m.d');
    }

    /**
     * Returns whether this is an autumn semester
     * (the first semester of the academic year).
     */
    public function isAutumn(): bool
    {
        return $this->part == 1;
    }

    /**
     * Returns whether this is a spring semester
     * (the second semester of the academic year).
     */
    public function isSpring(): bool
    {
        return $this->part == 2;
    }

    /**
     * Returns a semester's start date: the starting month's (based on constants) first week's last day.
     */
    public function getStartDate(): Carbon
    {
        $year = $this->year;
        if ($this->isSpring()) {
            $year += 1;
        }
        $month = $this->isAutumn() ? self::START_OF_AUTUMN_SEMESTER : self::START_OF_SPRING_SEMESTER;

        return Carbon::createFromDate($year, $month, 1)->endOfWeek();
    }

    /**
     * Returns a semester's end date: the month after the ending month's (based on constants) first week's last day.
     */
    public function getEndDate(): Carbon
    {
        $year = $this->year + 1; // end of semester is always in the next year
        $month = $this->isAutumn() ? self::END_OF_AUTUMN_SEMESTER : self::END_OF_SPRING_SEMESTER;

        return Carbon::createFromDate($year, $month, 1)->endOfWeek();
    }

    /**
     * Whether the semester is in the past.
     */
    public function isClosed(): bool
    {
        return Carbon::today() > $this->getEndDate();
    }

    /**
     * Returns the users with any status in the semester.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'semester_status')->withPivot(['status', 'verified', 'comment']);
    }

    /**
     * Returns the users with the specified status in the semester.
     */
    public function usersWithStatus($status): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'semester_status')
            ->wherePivot('status', '=', $status)
            ->withPivot('comment', 'verified');
    }

    /**
     * Returns the transactions made in the semester.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'semester_id');
    }

    /**
     * Returns the community services made in the semester.
     * @return HasMany
     */
    public function communityServices(): HasMany
    {
        return $this->hasMany(CommunityService::class, 'semester_id');
    }

    /**
     * Returns the transactions belonging to the checkout in the semester.
     *
     * @param Checkout $checkout
     */
    public function transactionsInCheckout(Checkout $checkout)
    {
        return $this->transactions()->where('checkout_id', $checkout->id);
    }

    /**
     * Returns the workshop balances in the semester.
     */
    public function workshopBalances(): HasMany
    {
        return $this->hasMany(WorkshopBalance::class);
    }

    /**
     * Returns the anonymous answer sheets filled as part of the evaluation form
     * in the semester.
     */
    public function answerSheets(): HasMany
    {
        return $this->hasMany(AnswerSheet::class);
    }

    /**
     * Returns the anonymous questions forming part of
     * the semester's evaluation form.
     */
    public function questions(): MorphMany
    {
        return $this->morphMany(Question::class, 'parent');
    }

    /**
     * The questions which a given user has not yet answered.
     * This returns a Collection.
     */
    public function questionsNotAnsweredBy(User $user)
    {
        return $this->questions()->whereDoesntHave('users', function ($query) use ($user) {
            $query->where('id', $user->id);
        })->get();
    }

    /**
     * Returns the current semester from cache.
     * There is always a "current" semester. If there is not in the database, this function creates it.
     * In case the current time is in between two semesters, it is still undefined as we follow the months and not the getEndDate/getStartDate.
     */
    public static function current(): Semester
    {
        $today = Carbon::today()->format('Ymd');
        if (!Cache::get('semester.current.' . $today)) {
            $now = Carbon::now();
            if ($now->month >= self::START_OF_SPRING_SEMESTER && $now->month <= self::END_OF_SPRING_SEMESTER) {
                $part = 2;
                $year = $now->year - 1;
            } else {
                $part = 1;
                // This assumes that the semester ends in the new year.
                $year = $now->month <= self::END_OF_AUTUMN_SEMESTER ? $now->year - 1 : $now->year;
            }
            $current = Semester::getOrCreate($year, $part);

            Cache::put('semester.current.' . $today, $current, $seconds = 10);
        }

        return Cache::get('semester.current.' . $today);
    }

    /**
     * Decides if the semester is equals with the current semester.
     */
    public function isCurrent(): bool
    {
        return $this->equals($this::current());
    }

    /**
     * Returns the next semester. If the next semester does not exist, creates it.
     */
    public function succ(): Semester
    {
        if ($this->isSpring()) {
            $year = $this->year + 1;
            $part = 1;
        } else {
            $year = $this->year;
            $part = 2;
        }

        return Semester::getOrCreate($year, $part);
    }

    /**
     * Returns the next semester. If the next semester does not exist, creates it.
     */
    public static function next(): Semester
    {
        return Semester::current()->succ();
    }

    /**
     * Returns the previous semester. If the previous semester does not exist, creates it.
     */
    public function pred(): Semester
    {
        if ($this->isSpring()) {
            $year = $this->year;
            $part = 1;
        } else {
            $year = $this->year - 1;
            $part = 2;
        }

        return Semester::getOrCreate($year, $part);
    }

    /**
     * Returns the previous semester. If the previous semester does not exist, creates it.
     */
    public static function previous(): Semester
    {
        return Semester::current()->pred();
    }

    /**
     * Gets or creates the semester.
     *
     * @param int $year
     * @param int $part (1,2)
     * @return Semester
     */
    public static function getOrCreate($year, $part): Semester
    {
        if (!in_array($part, [1, 2])) {
            throw new InvalidArgumentException("The semester's part is not 1 or 2.");
        }
        $semester = Semester::firstOrCreate([
            'year' => $year,
            'part' => (string)$part,
        ]);

        return $semester;
    }


    /* Helpers */

    /**
     * Semester is equal to the other semester.
     * @param Semester $other
     * @return bool
     */
    public function equals(Semester $other): bool
    {
        return $this->year == $other->year && $this->part == $other->part;
    }
}
