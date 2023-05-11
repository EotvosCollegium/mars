<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudyLine extends Model
{
    use HasFactory;

    protected $fillable = [
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
}
