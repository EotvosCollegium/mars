<?php

namespace App\Models;

use App\Utils\DataCompresser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property User $user
 * @property Collection $files
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

    public function getSemesterAverageAttribute($value): ?string
    {
        return DataCompresser::decompressData($value);
    }

    public function setSemesterAverageAttribute($value)
    {
        $this->attributes['semester_average'] = DataCompresser::compressData($value);
    }

    public function getLanguageExamAttribute($value): ?string
    {
        return DataCompresser::decompressData($value);
    }

    public function setLanguageExamAttribute($value)
    {
        $this->attributes['language_exam'] = DataCompresser::compressData($value);
    }

    public function getCompetitionAttribute($value): ?string
    {
        return DataCompresser::decompressData($value);
    }

    public function setCompetitionAttribute($value)
    {
        $this->attributes['competition'] = DataCompresser::compressData($value);
    }

    public function getPublicationAttribute($value): ?string
    {
        return DataCompresser::decompressData($value);
    }

    public function setPublicationAttribute($value)
    {
        $this->attributes['publication'] = DataCompresser::compressData($value);
    }

    public function getForeignStudiesAttribute($value): ?string
    {
        return DataCompresser::decompressData($value);
    }

    public function setForeignStudiesAttribute($value)
    {
        $this->attributes['foreign_studies'] = DataCompresser::compressData($value);
    }

    /*
    |--------------------------------------------------------------------------
    | Public functions
    |--------------------------------------------------------------------------
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
        if (! isset($educationalInformation->program)) {
            return false;
        }
        if (! isset($this->graduation_average)) {
            return false;
        }
        if (! isset($this->question_1)) {
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
