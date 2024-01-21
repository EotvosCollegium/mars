<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\LanguageExam
 *
 * @property string $path
 * @property string $language
 * @property string $level
 * @property string $type
 * @property Carbon $date
 * @property bool $wasBeforeEnrollment
 * @property int $id
 * @property int $educational_information_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EducationalInformation|null $educationalInformation
 * @property-read bool $was_before_enrollment
 * @method static \Database\Factories\LanguageExamFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam query()
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam whereEducationalInformationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LanguageExam whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LanguageExam extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'date'
    ];

    protected $fillable = [
        'path',
        'language',
        'level',
        'type',
        'date'
    ];

    /**
     * The educational information that belong to the language exam.
     */
    public function educationalInformation(): BelongsTo
    {
        return $this->belongsTo(EducationalInformation::class);
    }

    /**
     * return true if the date is before the user's enrollment date.
     * @return Attribute
     */
    public function wasBeforeEnrollment(): Attribute
    {
        return Attribute::make(
            get: fn (): bool =>
                $this->date->lt(
                    Carbon::createFromDate($this->educationalInformation->year_of_acceptance, 9, 1)
                )
        );
    }
}
