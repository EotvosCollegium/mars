<?php

namespace App\Models\AnonymousQuestions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Semester;
use App\Models\GeneralAssemblies\QuestionOption;
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
        if (is_null($semester)) $semester = Semester::current();

        return $semester->answerSheets()->create([
            'year_of_acceptance' => $user->educationalInformation->year_of_acceptance
        ]);
    }
}
