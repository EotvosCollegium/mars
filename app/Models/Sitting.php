<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Question;

class Sitting extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['title', 'opened_at', 'closed_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

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
        if ($this->isOpen() || $this->isClosed()) {
            throw new Exception("tried to open sitting when it has already been opened");
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
        foreach ($this->questions() as $question) {
            if ($question->isOpen()) {
                $question->close();
                $question->save();
            }
        }
        $this->closed_at=now();
    }
    public function addQuestion(string $title, int $max_options=1, $opened_at=null, $closed_at=null): Question
    {
        return Question::create([
            'sitting_id' => $this->id,
            'title' => $title,
            'max_options' => $max_options,
            'opened_at' => $opened_at,
            'closed_at' => $closed_at
        ]);
    }
    public function questions()
    {
        return $this->hasMany(Question::class)->orderByDesc('opened_at')->get();
    }
}
