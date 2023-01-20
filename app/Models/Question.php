<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use App\Models\Option;
use App\Models\User;


class Question extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function hasBeenOpened(): bool {
        return $this->opened_at!=null && $this->opened_at<=now();
    }
    public function isOpen(): bool {
        return $this->hasBeenOpened() &&
                !$this->isClosed();
    }
    public function isClosed(): bool {
        return $this->closed_at!=null && $this->closed_at<=now();
    }
    public function open(): void {
        if (!$this->belongsTo(Sitting::class)->isOpen()) throw new Exception("tried to open question when sitting was not open");
        if (isOpen() || isClosed()) throw new Exception("tried to open question when it has already been opened");
        $this->opened_at=now();
    }
    public function closed(): void {
        if (isClosed()) throw new Exception("tried to close sitting when it has already been closed");
        if (!isOpen()) throw new Exception("tried to close sitting when it was not open");
        $this->closed_at=now();
    }
    public function addOption(string $title): Option {
        return Option::create([
            'question_id' => $this->id,
            'title' => $title,
            'votes' => 0
        ]);
    }
    public function hasVoted(User $user): bool {
        return DB::table('question_user')->where('question_id', $this->id)
                ->where('user_id', $user->id)->exists();
    }
}
