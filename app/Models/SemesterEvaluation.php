<?php

namespace App\Models;

use App\Utils\DataCompresser;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property int $user_id
 * @property User $user
 * @property int $semester_id
 * @property Semester $semester
 * @property string $alfonso_note
 * @property array $courses
 * @property string $courses_note
 * @property string $current_avg
 * @property string $last_avg
 * @property string $general_assembly_note
 * @property array $professional_results
 * @property array $research
 * @property array $publications
 * @property array $conferences
 * @property array $scholarships
 * @property array $educational_activity
 * @property array $public_life_activities
 * @property bool $can_be_shared
 * @property string $feedback
 * @property bool $resign_residency
 * @property string $next_status
 * @property string $next_status_note
 * @property bool $will_write_request
 *
 */
class SemesterEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'semester_id',
        'alfonso_note',
        'courses',
        'courses_note',
        'current_avg',
        'last_avg',
        'general_assembly_note',
        'professional_results',
        'research',
        'publications',
        'conferences',
        'scholarships',
        'educational_activity',
        'public_life_activities',
        'can_be_shared',
        'feedback',
        'resign_residency',
        'next_status',
        'next_status_note',
        'will_write_request',
    ];

    protected $casts = [
        'can_be_shared' => 'boolean',
        'resign_residency' => 'boolean',
        'will_write_request' => 'boolean',
    ];

    /**
     * The user who made the evaluation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The semester in question.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Convert the courses attribute to a JSON representation (in db).
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function courses(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true) ?? [],
            set: fn ($value) => json_encode($value),
        );
    }

    /**
     * Get last_avg attribute. If null, search for the current_avg in last semester.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function lastAvg(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? $value :
                $this->user->semesterEvaluations()
                ->where('semester_id', Semester::previous()->id)
                ->first()
                ?->current_avg,
            set: fn ($value) => $value,
        );
    }

    /**
     * Get/Set the professonal_results attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function professionalResults(): Attribute
    {
        return Attribute::make(
            get: fn ($value) : array => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get/Set the research attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function research(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get/Set the publications attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function publications(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get/Set the conferences attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function conferences(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get/Set the scholarships attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function scholarships(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get/Set the educational_activity attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function educationalActivity(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }

    /**
     * Get/Set the public_life_activities attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function publicLifeActivities(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => DataCompresser::decompressData($value),
            set: fn ($value) => DataCompresser::compressData($value),
        );
    }
}
