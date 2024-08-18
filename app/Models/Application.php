<?php

namespace App\Models;

use App\Utils\DataCompresser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * App\Models\Application
 *
 * @property User $user
 * @property Collection $files
 * @property boolean $submitted
 * @property string $graduation_average
 * @property boolean $applied_for_resident_status
 * @property boolean $admitted_for_resident_status
 * @property array $semester_average
 * @property array $language_exam
 * @property array $competition
 * @property array $publication
 * @property array $foreign_studies
 * @property array $question_1
 * @property string $question_1_custom
 * @property string $question_2
 * @property string $question_3
 * @property string $question_4
 * @property boolean $accommodation
 * @property string $present
 * @property string $note
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int|null $files_count
 * @property-read string $question1_custom
 * @method static \Illuminate\Database\Eloquent\Builder|Application newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Application newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Application query()
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereAccommodation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereCompetition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereForeignStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereGraduationAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereLanguageExam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application wherePresent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application wherePublication($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereQuestion1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereQuestion2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereQuestion3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereQuestion4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereSemesterAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Application whereUserId($value)
 * @mixin \Eloquent
 */
class Application extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'submitted',
        'applied_for_resident_status',
        'admitted_for_resident_status',
        'graduation_average',
        'semester_average',
        'language_exam',
        'competition',
        'publication',
        'foreign_studies',
        'question_1',
        'question_2',
        'question_3',
        'question_4',
        'accommodation',
        'present',
        'note'
    ];

    protected $casts = [
        'submitted' => 'bool',
        'applied_for_resident_status' => 'bool',
        'admitted_for_resident_status' => 'bool'
    ];

    public const QUESTION_1 = [
        "tanárom ajánlotta",
        "ismerősöm ajánlotta",
        "családtag/rokon ajánlotta",
        "voltam a Tehetségtáborban",
        "voltam a Természettudományos Táborban",
        "kifejezetten szakkollégiumokat kerestem",
        "kari, egyetemi nyílt napon vagy hasonló rendezvényen láttam",
        "a Facebook/Instagram hirdetést láttam",
        "egy Facebook oldalon vagy csoportban posztolták",
        "az ELTE honlapján olvastam róla"
    ];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    /**
     * The applicant User.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withoutGlobalScope('verified');
    }


    /**
     * The ApplicationWorkshop models that the user applied for (includes status of application).
     */
    public function applicationWorkshops(): HasMany
    {
        return $this->hasMany(ApplicationWorkshop::class);
    }


    /**
     * The Workshop models that the user applied for.
     * @return HasManyThrough
     */
    public function appliedWorkshops(): HasManyThrough
    {
        return $this->hasManyThrough(
            Workshop::class,
            ApplicationWorkshop::class,
            'application_id',
            'id',
            'id',
            'workshop_id'
        );
    }

    /**
     * The Workshop models that the user admitted to.
     */
    public function admittedWorkshops(): HasManyThrough
    {
        return $this->appliedWorkshops()->where('application_workshops.admitted', true);
    }

    /**
     * Uploaded files
     * @return HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany('App\Models\File');
    }


    /*
    |--------------------------------------------------------------------------
    | Local scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include applications admitted to any workshop.
     * @param Builder $query
     * @return Builder
     */
    public function scopeAdmitted(Builder $query): Builder
    {
        return $query->whereHas('applicationWorkshops', function ($query) {
            $query->where('admitted', true);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Get a bool whether the applicant has been admitted to any workshops.
     *
     * @return Attribute
     */
    protected function admitted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->applicationWorkshops()->where('admitted', true)->exists(),
        );
    }


    /**
     * Get a bool whether the applicant has been called in by any workshops.
     *
     * @return Attribute
     */
    protected function calledIn(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->applicationWorkshops()->where('called_in', true)->exists(),
        );
    }

    /**
     * Get/set the application's semester_average attribute.
     *
     * @return Attribute
     */
    protected function semesterAverage(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get/set the application's language_exam attribute.
     *
     * @return Attribute
     */
    protected function languageExam(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get/set the application's competition attribute.
     *
     * @return Attribute
     */
    public function competition(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get/set the application's publication attribute.
     *
     * @return Attribute
     */
    public function publication(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get/set the application's foreign_studies attribute.
     *
     * @return Attribute
     */
    public function foreignStudies(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get/set the application's question_1 attribute.
     *
     * @return Attribute
     */
    public function question1(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get the application's question_1_custom attribute.
     *
     * @return Attribute
     */
    public function question1Custom(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->getCustomValue($this->question_1, self::QUESTION_1)
        );
    }

    /**
     * Get a custom answer that is not listed in the possible answers.
     * @param array $answers
     * @param array $possible_answers
     * @return string
     */
    private function getCustomValue(array $answers = [], array $possible_answers = []): string
    {
        foreach ($answers as $answer) {
            if (!in_array($answer, $possible_answers)) {
                return $answer;
            }
        }
        return '';
    }

    /*
    |--------------------------------------------------------------------------
    | Public functions
    |--------------------------------------------------------------------------
    */

    /**
     * Determine whether the application is ready to submit.
     * @return array The missing data.
     */
    public function missingData(): array
    {
        $user = $this->user;
        $educationalInformation = $user->educationalInformation;
        $personalInformation = $user->personalInformation;

        $missingData = [];

        if (!isset($personalInformation)) {
            $missingData[] = 'Személyes adatok';
        }

        if (!isset($educationalInformation)) {
            $missingData[] = 'Tanulmányi adatok';
        }

        if (is_null($educationalInformation?->neptun)) {
            $missingData[] =  'Neptun-kód';
        }

        // @phpstan-ignore-next-line
        if ($educationalInformation?->studyLines->count() == 0) {
            $missingData[] =  'Megjelölt szak';
        }

        if (!isset($educationalInformation?->alfonso_language)) {
            $missingData[] =  'Megjelölt ALFONSÓ nyelv';
            //level is required when updating language
        }

        if (count($this->files) < 2) {
            $missingData[] =  'Legalább két feltöltött fájl';
        }

        if ($this->appliedWorkshops->count() == 0) {
            $missingData[] =  'Megjelölt műhely';
        }

        if ($user->faculties->count() == 0) {
            $missingData[] =  'Megjelölt kar';
        }

        if (!isset($this->graduation_average)) {
            $missingData[] =  'Érettségi átlaga';
        }
        if (!isset($this->applied_for_resident_status)) {
            $missingData[] =  'Megpályázni kívánt státusz';
        }
        if (!isset($this->question_1) || $this->question_1 == []) {
            $missingData[] =  '"Honnan hallott a Collegiumról?" kérdés';
        }
        if (!isset($this->question_2)) {
            $missingData[] =  '"Miért kíván a Collegium tagja lenni?" kérdés';
        }
        if (!isset($this->question_3)) {
            $missingData[] =  '"Tervez-e tovább tanulni a diplomája megszerzése után? Milyen tervei vannak az egyetem után?" kérdés';
        }

        return $missingData;
    }

    /**
     * Sync the applied workshops.
     * @param array|null $workshop_ids
     * @return void
     */
    public function syncAppliedWorkshops(?array $workshop_ids): void
    {
        foreach (Workshop::all() as $workshop) {
            if(in_array($workshop->id, $workshop_ids ?? [])) {
                // make sure applied workshop exists
                $this
                    ->applicationWorkshops()
                    ->updateOrCreate(['workshop_id' => $workshop->id]);
            } else {
                // delete application to workshop
                $this->applicationWorkshops()->where('workshop_id', $workshop->id)->delete();
            }
        }
    }

    /**
     * Return a list of users in the committee:
     * workshop leaders/administrators/committee members and aggregated committee members
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    public function committeeMembers()
    {
        return User::query()
            ->whereHas('roles', function ($query) {
                $query
                    ->whereIn('name', [Role::WORKSHOP_LEADER, Role::WORKSHOP_ADMINISTRATOR, Role::APPLICATION_COMMITTEE_MEMBER])
                    ->whereIn('workshop_id', $this->appliedWorkshops->pluck('id'));
            })->orWhereHas('roles', function ($query) {
                $query->where('name', Role::AGGREGATED_APPLICATION_COMMITTEE_MEMBER);
            })->get();
    }
}
