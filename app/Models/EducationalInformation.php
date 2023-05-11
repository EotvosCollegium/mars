<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array $program
 * @property string $programs
 * @property string $neptun
 *
 */
class EducationalInformation extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'year_of_graduation',
        'high_school',
        'neptun',
        'year_of_acceptance',
        'email',
        'program',
        'alfonso_language',
        'alfonso_desired_level',
        'alfonso_passed_by'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * The educational programs that belong to the educational information.
     */
    public function studyLines()
    {
        return $this->hasMany(StudyLine::class);
    }

    /**
     * The uploaded language exams that belong to the educational information.
     */
    public function languageExams()
    {
        return $this->hasMany(LanguageExam::class);
    }
}
