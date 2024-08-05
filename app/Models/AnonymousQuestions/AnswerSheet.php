<?php

namespace App\Models\AnonymousQuestions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Semester;
use App\Models\QuestionOption;
use App\Models\AnonymousQuestions\LongAnswer;

/**
 * Groups a collegist's answers to anonymous questions
 * of the evaluation form.
 */
class AnswerSheet extends Model
{
    use HasFactory;

    /**
     * For better anonymity,
     * this model should not be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'year_of_acceptance'
    ];

    /**
     * The semester in which the form was filled.
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * The answer options chosen by the filler.
     */
    public function chosenOptions(): BelongsToMany
    {
        return $this->belongsToMany(QuestionOption::class);
    }

    /**
     * The long answers given by the filler.
     */
    public function longAnswers(): HasMany
    {
        return $this->hasMany(LongAnswer::class);
    }

    /**
     * Create an answer sheet for the given user,
     * with their anonymous data.
     * The default semester is the current one.
     */
    public static function createForUser(User $user, Semester $semester = null): AnswerSheet
    {
        if (is_null($semester)) {
            $semester = Semester::current();
        }

        return $semester->answerSheets()->create([
            'year_of_acceptance' => $user->educationalInformation->year_of_acceptance
        ]);
    }

    /**
     * Create an answer sheet for the current user `user()`,
     * with their anonymous data.
     * The default semester is the current one.
     */
    public static function createForCurrentUser(Semester $semester = null): AnswerSheet
    {
        return self::createForUser(user(), $semester);
    }

    /**
     * Returns an array with the data associated with the sheet:
     * year of acceptance and then textual answers to the questions
     * in order of their ids.
     * (For multiple-choice questions, the separators between the choices are commas.)
     * The id of the sheet is omitted for better pseudonomity.
     */
    public function toArray(): array
    {
        $row = [
            $this->semester->tag,
            $this->year_of_acceptance
        ];
        foreach ($this->semester->questions()->orderBy('id')->get() as $question) {
            if ($question->has_long_answers) {
                $row[] = $this->longAnswers()
                                        ->where('question_id', $question->id)
                                        ->first()->text ?? '';
            } elseif ($question->isMultipleChoice()) {
                $row[] = $this->chosenOptions()
                                        ->where('question_id', $question->id)
                                        ->pluck('title')->implode('/');
            } else {
                $row[] = $this->chosenOptions()
                                        ->where('question_id', $question->id)
                                        ->first()->title ?? '';
            }
        }
        return $row;
    }
}
