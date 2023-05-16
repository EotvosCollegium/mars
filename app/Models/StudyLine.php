<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'educational_information_id',
        'name',
        'type',
        'start',
        'end'
    ];

    public const TYPES = [
        'bachelor' => 'BSc',
        'master' => 'MSc',
        'phd' => 'PhD',
        'ot' => 'OT',
        'other' => 'Other'
    ];

    public function startSemester()
    {
        return $this->belongsTo(Semester::class, 'start');
    }

    public function endSemester()
    {
        return $this->belongsTo(Semester::class, 'end');
    }

}
