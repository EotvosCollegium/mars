<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\StudyLine
 *
 * @property int $start
 * @property int $end
 * @property string $name
 * @property string $minor
 * @property string $type
 * @property Semester $startSemester
 * @property ?Semester $endSemester
 * @property int $id
 * @property int $educational_information_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\StudyLineFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine query()
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine whereEducationalInformationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine whereEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine whereMinor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine whereStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine currentlyEnrolled()
 * @method static \Illuminate\Database\Eloquent\Builder|StudyLine highestLevel()
 * @mixin \Eloquent
 */
class StudyLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'educational_information_id',
        'name',
        'minor',
        'type',
        'start',
        'end'
    ];

    public const TYPES = [
        'bachelor' => 'BA/BSc',
        'master' => 'MA/MSc',
        'phd' => 'PhD',
        'ot' => 'Osztatlan',
        'other' => 'Egyéb'
    ];

    /**
     * The EducationalInformation model to which the study line is attached.
     */
    public function educationalInformation(): BelongsTo
    {
        return $this->belongsTo(EducationalInformation::class);
    }

    /**
     * The first semester of studies.
     */
    public function startSemester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'start');
    }

    /**
     * The last semester of studies, if it has been finished (null otherwise).
     */
    public function endSemester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'end');
    }

    /**
     * The prettified name with all important information (without semester names).
     */
    public function getName(): string
    {
        $name = $this->name;
        if (isset($this->type) && $this->type != 'other') {
            $name .= ' ' . self::TYPES[$this->type];

        }
        if (isset($this->minor)) {
            $name .= ' - minor: ' . $this->minor;
        }
        return $name;
    }

    /**
     * The prettified name with all important information
     * (with the names of the first and last semesters).
     */
    public function getNameWithYear(): string
    {
        $name = $this->getName();
        if ($this->start) {
            $name .= ' (' . $this->startSemester->tag . ' - ' . $this->endSemester?->tag . ')';
        }
        return $name;
    }

    /**
     * Filter study lines to only include the ones that are not finished.
     * Finishing in the current semester is NOT considered finished.
     * NOTE: we do not yet filter on start date as this is rarely an issue.
     */
    public function scopeCurrentlyEnrolled(Builder $query): void
    {
        $oldSemesters = Semester::allUntilCurrent()->filter(function ($semester) {
            return $semester->id != Semester::current()->id;
        });
        $query->whereNull('end')->orWhereNotIn('end', $oldSemesters->pluck('id'));
    }

    /**
     * Order study lines by level with phd in front and bachelor at the end.
     * @param Builder $query
     * @return void
     */
    public function scopeOrderByLevel(Builder $query): void
    {
        $query->orderByRaw(
            "CASE WHEN type = 'bachelor' THEN 2
                      WHEN type = 'phd' THEN 0
                      ELSE 1 END"
        );
    }

}
