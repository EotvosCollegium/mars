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

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'sitting_id', 'max_options', 'opened_at', 'closed_at'];

    public $timestamps = false;

    public function sitting(): BelongsTo
    {
        return $this->belongsTo(Sitting::class);
    }
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class);
    }
    public function hasBeenOpened(): bool
    {
        return $this->opened_at!=null && $this->opened_at<=now();
    }
    public function isOpen(): bool
    {
        return $this->hasBeenOpened() &&
                !$this->isClosed();
    }
    public function isClosed(): bool
    {
        return $this->closed_at!=null && $this->closed_at<=now();
    }
    public function open(): void
    {
        if (!$this->sitting->isOpen()) {
            throw new Exception("tried to open question when sitting was not open");
        }
        if ($this->isOpen() || $this->isClosed()) {
            throw new Exception("tried to open question when it has already been opened");
        }
        $this->update(['opened_at'=>now()]);
    }
    public function close(): void
    {
        if ($this->isClosed()) {
            throw new Exception("tried to close sitting when it has already been closed");
        }
        if (!$this->isOpen()) {
            throw new Exception("tried to close sitting when it was not open");
        }
        $this->update(['closed_at'=>now()]);
    }
    public function isMultipleChoice(): bool
    {
        return $this->max_options>1;
    }
    public function hasVoted(User $user): bool
    {
        return QuestionUser::where('question_id', $this->id)->where('user_id', $user->id)->exists();
    }
}
