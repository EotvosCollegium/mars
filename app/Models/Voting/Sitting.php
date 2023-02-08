<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Voting\Question;

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

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
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
        if ($this->isOpen() || $this->isClosed()) {
            throw new \Exception("tried to open sitting when it has already been opened");
        }
        $this->update(['opened_at'=>now()]);
    }
    public function close(): void
    {
        if ($this->isClosed()) {
            throw new \Exception("tried to close sitting when it has already been closed");
        }
        if (!$this->isOpen()) {
            throw new \Exception("tried to close sitting when it was not open");
        }
        foreach ($this->questions()->get() as $question) {
            if ($question->isOpen()) {
                $question->close();
            }
        }
        $this->update(['closed_at'=>now()]);
    }
}
