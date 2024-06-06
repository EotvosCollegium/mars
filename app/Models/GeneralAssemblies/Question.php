<?php

namespace App\Models\GeneralAssemblies;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\GeneralAssemblies\QuestionOption;
use App\Models\GeneralAssemblies\QuestionUser;
use App\Models\AnonymousQuestions\AnswerSheet;
use App\Models\AnonymousQuestions\LongAnswer;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Throwable;

/**
 * App\Models\GeneralAssemblies\Question
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
 * @method static \Database\Factories\GeneralAssemblies\QuestionFactory factory(...$parameters)
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

    protected $fillable = ['title', 'sitting_id', 'max_options', 'opened_at', 'closed_at', 'has_long_answers'];

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
     * Whether the parent is a general assembly
     * or a semester (evaluation form).
     */
    public function isForAssembly(): bool
    {
        return 'App\\Models\\GeneralAssemblies\\GeneralAssembly' == $this->parent_type;
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
        return
            !$this->isForAssembly() ||
            ($this->opened_at != null && $this->opened_at <= now());
    }

    /**
     * @return bool Whether the question is currently open.
     */
    public function isOpen(): bool
    {
        return
            $this->isForAssembly()
            ? ($this->hasBeenOpened() && !$this->isClosed())
            : !$this->parent->isClosed();
    }

    /**
     * @return bool Whether the question has been closed.
     */
    public function isClosed(): bool
    {
        return
            $this->isForAssembly()
            ? ($this->closed_at != null && $this->closed_at <= now())
            : $this->parent->isClosed();
    }

    /**
     * Opens the question.
     * @throws Exception if it has already been opened.
     */
    public function open(): void
    {
        if (!$this->parent->isOpen()) {
            throw new Exception("tried to open question when general_assembly was not open");
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
     * Votes for an option or a list of given options in the name of the user.
     * @param User $user
     * @param QuestionOption|array $options
     * @throws Exception if an option does not belong to the question or if too many options are selected.
     * @throws Throwable
     */
    public function vote(User $user, QuestionOption|array $options): void
    {
        // if there is only one option given:
        if (!is_array($options)) $options = [$options];

        if (!$this->isOpen()) {
            throw new Exception("question not open");
        }
        if ($this->max_options < count($options)) {
            throw new Exception("too many options given");
        }
        // sort options to avoid deadlock
        usort($options, function ($a, $b) {
            return $a->id - $b->id;
        });
        DB::transaction(function () use ($user, $options) {
            QuestionUser::create([
                'question_id' => $this->id,
                'user_id' => $user->id,
            ]);
            foreach ($options as $option) {
                if ($option->question_id != $this->id) {
                    throw new Exception("Received an option which does not belong to the question");
                }
                $option->increment('votes');
            }
        });
    }

    /**
     * Adds answers to an anonymous answer sheet.
     * Throws if an option does not belong to the question or if too many options are selected,
     * or if this is not an anonymous question belonging to a semester.
     */
    public function giveAnonymousAnswer(User $user, AnswerSheet $answerSheet, array|QuestionOption $options): void
    {
        if ($this->isForAssembly()) {
            throw new \Exception("this question is not an anonymous feedback question");
        } else {
            // if there is only one option given:
            if (!is_array($options)) $options = [$options];

            // for the sake of the seeders,
            // we have to accept answers for closed questions too
            if (!$this->isOpen() && (!app()->runningInConsole() || app()->runningUnitTests())) {
                throw new Exception("question not open");
            }
            if ($this->max_options < count($options)) {
                throw new Exception("too many options given");
            }

            DB::transaction(function () use ($user, $answerSheet, $options) {
                QuestionUser::create([
                    'question_id' => $this->id,
                    'user_id' => $user->id,
                ]);
                foreach ($options as $option) {
                    if ($option->question_id != $this->id) {
                        throw new Exception("Received an option which does not belong to the question");
                    }
                    $option->increment('votes');
                    DB::table('answer_sheet_question_option')->insert([
                        'answer_sheet_id' => $answerSheet->id,
                        'question_option_id' => $option->id
                    ]);
                }
            });
        }
    }

    /**
     * Creates a long answer for the question
     * and records that the user has answered the question.
     * Throws if the question does not accept long answers.
     */
    public function giveLongAnswer(User $user, AnswerSheet $answerSheet, string $text): LongAnswer
    {
        if (!$this->has_long_answers) {
            throw new \Exception('this question does not accept long answers');
        } else {
            QuestionUser::create([
                'question_id' => $this->id,
                'user_id' => $user->id,
            ]);
            return $this->longAnswers()->create([
                'answer_sheet_id' => $answerSheet->id,
                'text' => $text
            ]);
        }
    }
}
