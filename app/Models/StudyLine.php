<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StudyLine
 *
 * @property int $start
 * @property int $end
 * @property string $name
 * @property string $minor
 * @property string $type
 * @property Semester $startSemester
 * @property Semester $endSemester
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

    public function startSemester()
    {
        return $this->belongsTo(Semester::class, 'start');
    }

    public function endSemester()
    {
        return $this->belongsTo(Semester::class, 'end');
    }

    public function getName(): string
    {
        $name = $this->name;
        if(isset($this->type) && $this->type != 'other') {
            $name .= ' '.self::TYPES[$this->type];

        }
        if(isset($this->minor)) {
            $name .= ' - minor: '.$this->minor;
        }
        return $name;
    }
    public function getNameWithYear(): string
    {
        $name = $this->getName();
        if($this->start) {
            $name .= ' ('.$this->startSemester->tag.' - '.$this->endSemester?->tag.')';
        }
        return $name;
    }

}
