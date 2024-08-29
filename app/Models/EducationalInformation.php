<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Semester;

/**
 * Either B2 or C1.
 */
enum LanguageExamLevel {
    case B2; case C1;
}

/**
 * App\Models\EducationalInformation
 *
 * @property StudyLine[]|Collection $studyLines
 * @property LanguageExam[]|Collection $languageExams
 * @property LanguageExam[]|Collection $languageExamsBeforeAcceptance
 * @property LanguageExam[]|Collection $languageExamsAfterAcceptance
 * @property User $user
 * @property string $year_of_graduation
 * @property string $high_school
 * @property string $neptun
 * @property int $year_of_acceptance
 * @property string $email
 * @property string $alfonso_language
 * @property string $alfonso_desired_level
 * @property int $id
 * @property int $user_id
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $alfonso_passed_by
 * @property string|null $research_topics
 * @property string|null $extra_information
 * @property-read int|null $language_exams_count
 * @property-read int|null $study_lines_count
 * @method static \Database\Factories\EducationalInformationFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation query()
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereAlfonsoDesiredLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereAlfonsoLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereAlfonsoPassedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereExtraInformation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereHighSchool($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereNeptun($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereResearchTopics($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereYearOfAcceptance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EducationalInformation whereYearOfGraduation($value)
 * @mixin \Eloquent
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
        return $this->languageExams()->where('date', '<', $acceptanceDate);
    }

    /**
     * Whether the user is a senior (i.e. currently has a PhD study line).
     */
    public function isSenior(): bool
    {
        return $this->studyLines()->currentlyEnrolled()->where('type', 'phd')->exists();
    }

    /**
     * Whether the user had neither bachelor nor teacher studies
     * when admitted
     * (so that they are exempt from alfonso requirements).
     */
    private function isMasterAdmittee(): bool
    {
        return
            // there is no study line that:
            $this->studyLines()
                // is bachelor or teacher
                ->where(function ($query) {$query->where('type', 'bachelor')->orWhere('type', 'ot');})
                ->whereHas('startSemester', function ($query) {
                    // here, we assume that the admittance semester is always an autumn semester
                    $query->where('year', '<', $this->year_of_acceptance)
                        ->orWhere(function ($query) {
                            $query->where('year', '=', $this->year_of_acceptance)
                                ->where('part', '=', 1);
                        });
                })->where(function ($query) {
                    // the end semester is either after/equal to the admittance semester
                    // or null
                    // here, we also assume that the admittance semester is always an autumn semester
                    $query->whereHas('endSemester', function ($query) {
                        $query->where('year', '>=', $this->year_of_acceptance);
                    })->orWhereNull('end');
            })->doesntExist();
    }

    /**
     * Whether the user is exempted from the start
     * (this includes seniors and those who have been admitted during their masters' studies).
     */
    public function alfonsoExempted(): bool
    {
        return $this->isSenior()
            || $this->isMasterAdmittee();
    }

    /**
     * @return array [language => required level] based on the entry level exams
     */
    public function alfonsoRequirements(): array
    {
        $entryLevel = $this->languageExamsBeforeAcceptance;

        $requirements = [];
        # default requirements without any language exams
        foreach (array_keys(config('app.alfonso_languages')) as $language) {
            $requirements[$language] = LanguageExamLevel::B2;
        }

        // @phpstan-ignore-next-line
        if ($entryLevel->count() >= 2) {
            foreach ($entryLevel as $exam) {
                if (!in_array($exam->level, ["C1", "C2"])) {
                    $requirements[$exam->language] = LanguageExamLevel::C1;
                } else {
                    unset($requirements[$exam->language]);
                }
            }
        }
        // @phpstan-ignore-next-line
        elseif ($entryLevel->count() == 1) {
            foreach ($entryLevel as $exam) {
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
        if ($this->alfonsoExempted()) return true;
        foreach ($this->alfonsoRequirements() as $language => $level) {
            if ($this->checkIfPassed($language, $level)) {
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
    private function checkIfPassed($language, LanguageExamLevel $level): bool
    {
        if ($level == LanguageExamLevel::B2) {
            $deadline = Carbon::createFromDate($this->year_of_acceptance + 3, 9, 1);
            $levels = ["B2", "C1", "C2"];
        } else {
            // for language teachers, this is actually 3 years; see rulebook
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
