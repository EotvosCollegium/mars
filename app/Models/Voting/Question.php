<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Models\Voting\Sitting;
use App\Models\Voting\QuestionOption;
use App\Models\Voting\QuestionUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'sitting_id', 'max_options', 'opened_at', 'closed_at'];

    public $timestamps = false;

    /**
     * @return BelongsTo The parent sitting.
     */
    public function sitting(): BelongsTo
    {
        return $this->belongsTo(Sitting::class);
    }

    /**
     * @return HasMany the options belonging to the question
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }

    /**
     * @return HasMany the users who voted on this question
     */
    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, QuestionUser::class, 'question_id', 'id', 'id', 'user_id');
    }

    /**
     * @return bool Whether the question has already been opened once (regardless of whether it has been closed since then).
     */
    public function hasBeenOpened(): bool
    {
        return $this->opened_at!=null && $this->opened_at<=now();
    }

    /**
     * @return bool Whether the question is currently open.*
     */
    public function isOpen(): bool
    {
        return $this->hasBeenOpened() &&
                !$this->isClosed();
    }
    /**
     * @return bool Whether the question has been closed.
     */
    public function isClosed(): bool
    {
        return $this->closed_at!=null && $this->closed_at<=now();
    }

    /**
     * Opens the question.
     * @throws Exception if it has already been opened.
     */
    public function open(): void
    {
        if (!$this->sitting->isOpen()) {
            throw new \Exception("tried to open question when sitting was not open");
        }
        if ($this->isOpen() || $this->isClosed()) {
            throw new \Exception("tried to open question when it has already been opened");
        }
        $this->update(['opened_at'=>now()]);
    }

    /**
     * Closes the question.
     * @throws Exception if it has already been closed or if it is not even open.
     */
    public function close(): void
    {
        if ($this->isClosed()) {
            throw new \Exception("tried to close sitting when it has already been closed");
        }
        if (!$this->isOpen()) {
            throw new \Exception("tried to close sitting when it was not open");
        }
        $this->update(['closed_at'=>now()]);
    }

    /**
     * @return book Whether the question is a multiple-choice question (with checkboxes).
     */
    public function isMultipleChoice(): bool
    {
        return $this->max_options>1;
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
     * Votes for a list of given options in the name of the user.
     * @param User $user
     * @param array $options QuestionOption array
     * @throws Exception if an option does not belong to the question or if too many options are selected.
     */
    public function vote(User $user, array $options): void
    {
        if (!$this->isOpen()) {
            throw new \Exception("question not open");
        }
        if ($this->max_options < count($options)) {
            throw new \Exception("too many options given");
        }
        DB::transaction(function () use ($user, $options) {
            QuestionUser::create([
                'question_id' => $this->id,
                'user_id' => $user->id,
            ]);
            foreach ($options as $option) {
                if ($option->question_id != $this->id) {
                    throw new \Exception("Received an option which does not belong to the question");
                }
                $option->increment('votes');
            }
        });
    }
}
