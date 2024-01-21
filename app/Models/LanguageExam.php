<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
* @property string $path
* @property string $language
* @property string $level
* @property string $type
* @property Carbon $date
* @property bool $wasBeforeEnrollment
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
