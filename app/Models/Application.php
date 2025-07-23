<?php

namespace App\Models;

use App\Utils\DataCompresser;
use App\Enums\FileType;
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
 * @property boolean $publication_consent
 * @property string $pseudonym
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
        'publication_consent',
        'pseudonym',
        'note'
    ];

    protected $casts = [
        'submitted' => 'bool',
        'applied_for_resident_status' => 'bool',
        'admitted_for_resident_status' => 'bool',
        'publication_consent' => 'bool',
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

    /**
     * Get files of a specific type.
     * @param FileType $type
     * @return HasMany
     */
    public function filesOfType(FileType $type): HasMany
    {
        return $this->files()->where('type', $type);
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

        if (!isset($user->name)) {
            $missingData[] = 'Személyes adat: név';
        }
        if (!isset($user->email)) {
            $missingData[] = 'Személyes adat: e-mail';
        }
        foreach (['place_of_birth', 'date_of_birth', 'mothers_name', 'phone_number',
                 'country', 'county', 'zip_code', 'city', 'street_and_number'] as $personal_info_field) {
            if (!isset($personalInformation) || !isset($personalInformation[$personal_info_field])) {
                $missingData[] = "Személyes adat: ".strtolower(__('user.'.$personal_info_field));
            }
        }
        foreach (['high_school', 'year_of_graduation', 'year_of_acceptance', 'neptun'] as $educational_info_field) {
            if (!isset($educationalInformation) || !isset($educationalInformation[$educational_info_field])) {
                $missingData[] = "Tanulmányi adat: ".strtolower(__('user.'.$educational_info_field));
            }
        }

        if ($user->faculties->count() == 0) {
            $missingData[] =  'Tanulmányi adat: megjelölt kar';
        }


        if (!isset($educationalInformation) || $educationalInformation->studyLines()->count() == 0) {
            $missingData[] =  'Tanulmányi adat: megjelölt szak';
        } else {
            foreach ($educationalInformation->studyLines as $study_line) {
                if (!isset($study_line['name'])) {
                    $missingData[] =  'Tanulmányi adat: valamely tanult szak adata: '.strtolower(__('user.study_line'));
                }
                if (!isset($study_line['type'])) {
                    $missingData[] =  'Tanulmányi adat: valamely tanult szak adata: '.strtolower(__('user.study_line_level'));
                }
                if (!isset($study_line['training_code'])) {
                    $missingData[] =  'Tanulmányi adat: valamely tanult szak adata: '.strtolower(__('user.study_line_training_code'));
                }
                if (!isset($study_line['start'])) {
                    $missingData[] =  'Tanulmányi adat: valamely tanult szak adata: '.strtolower(__('user.study_line_start'));
                }
            }
        }
        if (isset($educationalInformation) && isset($educationalInformation->year_of_acceptance)) {
            if (!isset($educationalInformation->alfonso_language) && !$educationalInformation->alfonsoExempted()) {
                $missingData[] =  'Tanulmányi adat: megjelölt ALFONSÓ nyelv';
            }
            if (!isset($educationalInformation->alfonso_desired_level) && !$educationalInformation->alfonsoExempted()) {
                $missingData[] =  'Tanulmányi adat: elérni kívánt ALFONSÓ szint';
            }
        }

        if (!isset($this->graduation_average)) {
            $missingData[] =  'Szakmai és motivációs kérdések: érettségi átlaga';
        }

        if (!isset($this->applied_for_resident_status)) {
            $missingData[] =  'Szakmai és motivációs kérdések: megpályázni kívánt státusz';
        }

        if ($this->appliedWorkshops->count() == 0) {
            $missingData[] =  'Szakmai és motivációs kérdések: megpályázni kívánt műhely';
        }

        if (!isset($this->question_1) || $this->question_1 == []) {
            $missingData[] =  'Szakmai és motivációs kérdések: "Honnan hallott a Collegiumról?" kérdés';
        }
        if (!isset($this->question_2)) {
            $missingData[] =  'Szakmai és motivációs kérdések: "Miért kíván a Collegium tagja lenni?" kérdés';
        }
        if ($this->question_2 && mb_strlen($this->question_2) < 500) {
            $missingData[] =  'Szakmai és motivációs kérdések: "Miért kíván a Collegium tagja lenni?" kérdésre adott válasz túl rövid (min. 500 leütés)';
        }
        if (!isset($this->question_3)) {
            $missingData[] =  'Szakmai és motivációs kérdések: "Tervez-e továbbtanulni a diplomája megszerzése után? Milyen tervei vannak az egyetem után?" kérdés';
        }

        if (!$this->publication_consent && !isset($this->pseudonym)) {
            $missingData[] =  'Szakmai és motivációs kérdések: jelige';
        }

        foreach (FileType::cases() as $type) {
            if ($this->needsFile($type) && !$this->filesOfType($type)->exists()) {
                $missingData[] = 'Szükséges dokumentum: ' . __('document.file_types.' . $type->value, [], 'hu');
            }
        }

        return $missingData;
    }

    /**
     * Determine whether the applicant needs to upload a file of a specific type.
     * @param FileType $type
     * @return bool
     */
    public function needsFile(FileType $type): bool
    {
        $semester = app(\App\Http\Controllers\Auth\ApplicationController::class)->semester();
        switch ($type) {
            case FileType::RESUME:
                return true;
            case FileType::BESOROLASI_HATAROZAT:
                return $this->user->educationalInformation->studyLines()->where('start', '=', $semester->id)->exists();
            case FileType::ERETTSEGI:
                return $this->user->educationalInformation->studyLines()->where('start', '=', $semester->id)->whereIn('type', ['bachelor', 'ot', 'other'])->exists();
            case FileType::ELVEGZETT_FELEV:
                return $this->user->educationalInformation->studyLines()->where('start', '<>', $semester->id)->exists();
            case FileType::DIPLOMA:
                return $this->user->educationalInformation->studyLines()->whereNotNull('end')->exists();
            default:
                return false;
        }
    }

    /**
     * Sync the applied workshops.
     * @param array|null $workshop_ids
     * @return void
     */
    public function syncAppliedWorkshops(?array $workshop_ids): void
    {
        foreach (Workshop::all() as $workshop) {
            if (in_array($workshop->id, $workshop_ids ?? [])) {
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
