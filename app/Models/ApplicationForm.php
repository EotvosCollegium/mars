<?php

namespace App\Models;

use App\Utils\DataCompresser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
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
    public const STATUS_BANISHED = 'banished';

    public const STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_IN_PROGRESS,
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
     * @return boolean
     */
    public function isReadyToSubmit(): bool
    {
        $user = $this->user;
        $educationalInformation = $user->educationalInformation;

        if (! isset($educationalInformation)) {
            return false;
        }

        if (! isset($user->profilePicture)) {
            return false;
        }
        if (count($this->files) < 2) {
            return false;
        }

        if ($user->workshops->count() == 0) {
            return false;
        }
        if ($user->faculties->count() == 0) {
            return false;
        }

        if (!$user->isResident() && !$user->isExtern()) {
            return false;
        }

        if (! isset($educationalInformation->year_of_graduation)) {
            return false;
        }
        if (! isset($educationalInformation->high_school)) {
            return false;
        }
        if (! isset($educationalInformation->neptun)) {
            return false;
        }
        if (! isset($educationalInformation->year_of_acceptance)) {
            return false;
        }
        if (! isset($educationalInformation->email)) {
            return false;
        }
        if (! isset($educationalInformation->program) || $educationalInformation->program == []) {
            return false;
        }
        if (! isset($this->graduation_average)) {
            return false;
        }
        if (! isset($this->question_1) || $this->question_1 == []) {
            return false;
        }
        if (! isset($this->question_2)) {
            return false;
        }
        if (! isset($this->question_3)) {
            return false;
        }

        return true;
    }
}
