<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $start
 * @property int $end
 * @property string $name
 * @property string $minor
 * @property string $type
 * @property Semester $startSemester
 * @property Semester $endSemester
 * @method getName()
 * @method getNameWithYear()
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
