<?php

namespace App\Models;

use App\Utils\DataCompresser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * App\Models\ApplicationForm
 *
 * @property User $user
 * @property Collection $files
 * @property string $status
 * @property string $graduation_average
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
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm query()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereAccommodation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereCompetition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereForeignStudies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereGraduationAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereLanguageExam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm wherePresent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm wherePublication($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereQuestion1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereQuestion2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereQuestion3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereQuestion4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereSemesterAverage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationForm whereUserId($value)
 * @mixin \Eloquent
 */
class ApplicationForm extends Model
{
    use HasFactory;

    protected $table = 'application_forms';

    protected $fillable = [
        'user_id',
        'status',
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

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_CALLED_IN = 'called_in';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_BANISHED = 'banished';

    public const STATUSES = [
        self::STATUS_IN_PROGRESS,
        self::STATUS_SUBMITTED,
        self::STATUS_CALLED_IN,
        self::STATUS_ACCEPTED,
        self::STATUS_BANISHED,
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

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\User')->withoutGlobalScope('verified');
    }

    public function files(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\File');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators
    |--------------------------------------------------------------------------
    */

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

        if (! isset($personalInformation)) {
            $missingData[] = 'Személyes adatok';
        }

        if (! isset($educationalInformation)) {
            $missingData[] = 'Tanulmányi adatok';
        }

        if ($educationalInformation?->studyLines->count() == 0) {
            $missingData[] =  'Megjelölt szak';
        }

        if (! isset($educationalInformation?->alfonso_language)) {
            $missingData[] =  'Megjelölt ALFONSÓ nyelv';
            //level is required when updating language
        }

        if (! isset($user->profilePicture)) {
            $missingData[] =  'Profilkép';
        }
        if (count($this->files) < 2) {
            $missingData[] =  'Legalább két feltöltött fájl';
        }

        if ($user->workshops->count() == 0) {
            $missingData[] =  'Megjelölt műhely';
        }

        if ($user->faculties->count() == 0) {
            $missingData[] =  'Megjelölt kar';
        }

        if (!$user->isResident() && !$user->isExtern()) {
            $missingData[] =  'Megjelölt collegista státusz';
        }

        if (! isset($this->graduation_average)) {
            $missingData[] =  'Érettségi átlaga';
        }
        if (! isset($this->question_1) || $this->question_1 == []) {
            $missingData[] =  '"Honnan hallott a Collegiumról?" kérdés';
        }
        if (! isset($this->question_2)) {
            $missingData[] =  '"Miért kíván a Collegium tagja lenni?" kérdés';
        }
        if (! isset($this->question_3)) {
            $missingData[] =  '"Tervez-e tovább tanulni a diplomája megszerzése után? Milyen tervei vannak az egyetem után?" kérdés';
        }

        return $missingData;
    }
}
