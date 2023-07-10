<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property StudyLine[]|Collection $studyLines
 * @property LanguageExam[]|Collection $languageExams
 * @property LanguageExam[]|Collection $languageExamsBeforeAcceptance
 * @property LanguageExam[]|Collection $languageExamsAfterAcceptance
 * @property User $user
 * @property string $year_of_graduation
 * @property string $high_school
 * @property string $neptun
 * @property string $year_of_acceptance
 * @property string $email
 * @property string $alfonso_language
 * @property string $alfonso_desired_level
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
    public function studyLines(): HasMany
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

    public function languageExamsAfterAcceptance()
    {
        $acceptanceDate = Carbon::createFromDate($this->year_of_acceptance, 9, 1);
        return $this->languageExams()->where('date', '>=', $acceptanceDate);
    }

    public function languageExamsBeforeAcceptance()
    {
        $acceptanceDate = Carbon::createFromDate($this->year_of_acceptance, 9, 1);
        return $this->languageExams()->where('date', '<=', $acceptanceDate);
    }


    /**
     * @return array [language => required level] based on the entry level exams
     */
    public function alfonsoRequirements(): array
    {
        $entryLevel = $this->languageExamsBeforeAcceptance;

        $requirements = [];
        # default requirements without any language exams
        foreach(array_keys(config('app.alfonso_languages')) as $language) {
            $requirements[$language] = "B2";
        }

        if ($entryLevel->count() >= 2) {
            foreach($entryLevel as $exam) {
                if(!in_array($exam->level, ["C1", "C2"])) {
                    $requirements[$exam->language] = "C1";
                } else {
                    unset($requirements[$exam->language]);
                }
            }
        }
        if($entryLevel->count() == 1) {
            foreach($entryLevel as $exam) {
                unset($requirements[$exam->language]);
            }
        }
        return $requirements;
    }

    /**
     * @return bool true if the collegist has passed the required language exams
     */
    public function alfonsoCompleted(): bool
    {
        foreach ($this->alfonsoRequirements() as $language => $level) {
            if($this->checkIfPassed($language, $level)) {
                return true;
            }
        }
        return false;
    }

     /**
     * @return bool true if the collegist can complete the requirements in the future
     */
    public function alfonsoCanBeCompleted(): bool
    {
        //a B2 language exam can always be passed in 3 years
        return now() < Carbon::createFromDate($this->year_of_acceptance + 3, 9, 1);
    }

    /**
     * @return bool check if a language exam is passed at least in the given level
     */
    private function checkIfPassed($language, $level): bool
    {
        if($level == "B2") {
            $deadline = Carbon::createFromDate($this->year_of_acceptance + 3, 9, 1);
            $levels = ["B2", "C1", "C2"];
        } else {
            $deadline = Carbon::createFromDate($this->year_of_acceptance + 2, 9, 1);
            $levels = ["C1", "C2"];
        }
        return $this->languageExamsAfterAcceptance()
                ->where('language', $language)
                ->whereIn('level', $levels)
                ->where('date', '<=', $deadline)
                ->exists();
    }
}
