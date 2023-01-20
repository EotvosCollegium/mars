<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Question;

class Sitting extends Model
{
    use HasFactory;

    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

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
        if (isOpen() || isClosed()) throw new Exception("tried to open sitting when it has already been opened");
        $this->opened_at=now();
    }
    public function closed(): void {
        if (isClosed()) throw new Exception("tried to close sitting when it has already been closed");
        if (!isOpen()) throw new Exception("tried to close sitting when it was not open");
        $this->closed_at=now();
    }
    public function addQuestion(string $title): Question {
        return Question::create([
            'sitting_id' => $this->id,
            'title' => $title
        ]);
    }
}
