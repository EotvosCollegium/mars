<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use App\Models\Sitting;
use App\Models\QuestionOption;
use App\Models\User;

class Question extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'sitting_id', 'max_options', 'opened_at', 'closed_at'];

    public $timestamps = false;

    public function sitting(): Sitting
    {
        return $this->belongsTo(Sitting::class)->first();
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
        if (!$this->sitting()->isOpen()) {
            throw new Exception("tried to open question when sitting was not open");
        }
        if ($this->isOpen() || $this->isClosed()) {
            throw new Exception("tried to open question when it has already been opened");
        }
        $this->opened_at=now();
    }
    public function close(): void
    {
        if ($this->isClosed()) {
            throw new Exception("tried to close sitting when it has already been closed");
        }
        if (!$this->isOpen()) {
            throw new Exception("tried to close sitting when it was not open");
        }
        $this->closed_at=now();
    }
    public function addOption(string $title): QuestionOption
    {
        return QuestionOption::create([
            'question_id' => $this->id,
            'title' => $title,
            'votes' => 0
        ]);
    }
    public function isMultipleChoice(): bool
    {
        return $this->max_options>1;
    }
    public function hasVoted(User $user): bool
    {
        return DB::table('question_user')->where('question_id', $this->id)
                ->where('user_id', $user->id)->exists();
    }
    public function canVote(User $user): bool
    {
        return $this->isOpen() && $user->isCollegist() && $user->isActive() && !$this->hasVoted($user);
    }
    public function getOptions()
    {
        return $this->hasMany(QuestionOption::class)->get();
    }
}
