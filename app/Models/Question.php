<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Throwable;

use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\QuestionOption;
use App\Models\QuestionUser;
use App\Models\AnonymousQuestions\AnswerSheet;
use App\Models\AnonymousQuestions\LongAnswer;
use App\Models\User;
use App\Models\Semester;

/**
 * App\Models\Question
 *
 * A question fillable by collegists,
 * either belonging to a semester's evaluation form
 * or to a general assembly.
 * Answers are anonymous;
 * only the fact that a user has answered
 * gets stored
 * (in the question_user table).
 * Can have QuestionOptions (with a counter)
 * with the number of options to choose in a single answer
 * (if this is greater than 1, the question is multiple-choice).
 * Can alternatively have a possibly to give long textual answers.
 *
 * @property int $id
 * @property int $general_assembly_id
 * @property string $title
 * @property int $max_options
 * @property string|null $opened_at
 * @property string|null $closed_at
 * @property-read GeneralAssembly|Semester $parent
 * @property-read Collection|QuestionOption[] $options
 * @property-read int|null $options_count
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\QuestionFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Question newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Question newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Question query()
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereGeneralAssemblyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereMaxOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Question whereTitle($value)
 * @mixin \Eloquent
 */
class Question extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'max_options', 'opened_at', 'closed_at', 'has_long_answers'];

    public $timestamps = false;

    /**
     * The parent (either a general assembly or a semester
     *                               having an evaluation form).
     * @return MorphTo
     */
    public function parent(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany the options belonging to the question
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    /**
     * The answers belonging to a question
     * which expects long text answers.
     */
    public function longAnswers(): HasMany
    {
        return $this->hasMany(LongAnswer::class);
    }

    /**
     * @return BelongsToMany the users who voted on this question
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, QuestionUser::class, 'question_id', 'user_id');
    }

    /**
     * @return bool Whether the question has already been opened once (regardless of whether it has been closed since then).
     */
    public function hasBeenOpened(): bool
    {
        return $this->opened_at != null && $this->opened_at <= now();
    }

    /**
     * @return bool Whether the question is currently open.
     */
    public function isOpen(): bool
    {
        return $this->hasBeenOpened() && !$this->isClosed();
    }

    /**
     * @return bool Whether the question has been closed.
     */
    public function isClosed(): bool
    {
        return $this->closed_at != null && $this->closed_at <= now();
    }

    /**
     * Opens the question.
     * @throws Exception if it has already been opened.
     */
    public function open(): void
    {
        if (!$this->parent->isOpen()) {
            throw new Exception("tried to open question when the parent was not open");
        }
        if ($this->isOpen() || $this->isClosed()) {
            throw new Exception("tried to open question when it has already been opened");
        }
        $this->update(['opened_at' => now()]);
    }

    /**
     * Closes the question.
     * @throws Exception if it has already been closed or if it is not even open.
     */
    public function close(): void
    {
        if ($this->isClosed()) {
            throw new Exception("tried to close question when it has already been closed");
        }
        if (!$this->hasBeenOpened()) {
            throw new Exception("tried to close question when it has not been opened");
        }
        $this->update(['closed_at' => now()]);
    }

    /**
     * @return bool Whether the question is a multiple-choice question (with checkboxes).
     */
    public function isMultipleChoice(): bool
    {
        return $this->max_options > 1;
    }

    /**
     * @param User $user
     * @return bool Whether a certain user has already voted in the question.
     */
    public function hasVoted(User $user): bool
    {
        return $this->users()->where('users.id', $user->id)->exists();
    }

    /**
     * Stores an answer (one or more options, or a long textual answer)
     * for the question.
     * If an answer sheet is provided,
     * it also appends the answer to it.
     * Throws if an option does not belong to the question,
     * if too many options are selected,
     * if the question is closed,
     * if the user has already answered the question
     * or if a long textual answer is provided for a question which does not support it.
     */
    public function storeAnswers(User $user, QuestionOption|array|string $answer, ?AnswerSheet $answerSheet = null): void
    {
        // the additional check is needed for the seeder
        if (!$this->isOpen() && (!app()->runningInConsole() || app()->runningUnitTests())) {
            throw new Exception("Tried to store answers for a question which is not open");
        }

        DB::transaction(function () use ($user, $answer, $answerSheet) {
            try {
                // For some reason, it seems to be stable
                // only if we manipulate the database directly.
                DB::table('question_user')->insert(['question_id' => $this->id, 'user_id' => $user->id]);
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                throw new Exception("The user has already answered this question");
            }

            // if we get only one option:
            if ($answer instanceof QuestionOption) {
                $answer = [$answer];
            }

            if (is_array($answer)) {
                if (count($answer) > $this->max_options) {
                    throw new Exception("More answers given then allowed ({$this->max_options})");
                }
                foreach ($answer as $option) {
                    if ($option->question_id != $this->id) {
                        throw new Exception("Received an option which does not belong to the question");
                    }
                    $option->increment('votes');

                    if (isset($answerSheet)) {
                        DB::table('answer_sheet_question_option')->insert([
                            'answer_sheet_id' => $answerSheet->id,
                            'question_option_id' => $option->id
                        ]);
                    }
                }
            } // else it is a string
            elseif (!$this->has_long_answers) {
                throw new Exception("This question does not support long answers");
            } else {
                $this->longAnswers()->create([
                    'answer_sheet_id' => $answerSheet->id,
                    'text' => $answer
                ]);
            }
        });
    }

    /**
     * Returns the key used in the POST request
     * for the inputs belonging to the question.
     * Used because numeric keys in arrays
     * would confuse PHP.
     */
    public function formKey(): string
    {
        return "q{$this->id}";
    }

    /**
     * The validation rules to be included
     * for the answer we get to the question
     * (the item in the request
     * with the id $this->formKey()).
     * Contains a rule with the key $this->formKey()
     * and for multiple-choice questions,
     * one with the key `$this->formKey() . '.*'`.
     */
    public function validationRules(): array
    {
        $key = $this->formKey();
        $rules = [];
        if ($this->has_long_answers) {
            $rules[$key] = 'required|string';
        } elseif ($this->isMultipleChoice()) {
            $rules[$key] = [
                'required',
                'array',
                'max:' . $this->max_options
            ];
            $rules[$key . '.*'] = Rule::in($this->options->map(
                function (QuestionOption $option) {return $option->id;}
            ));
        } else {
            $rules[$key] = [
                'required',
                Rule::in($this->options->map(
                    function (QuestionOption $option) {return $option->id;}
                ))
            ];
        }
        return $rules;
    }
}
