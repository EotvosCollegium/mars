<?php

namespace App\Models\Voting;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Voting\Question;
use App\Models\Voting\QuestionUser;
use App\Models\User;

class QuestionOption extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['question_id', 'title', 'votes'];

    /**A query for the question the option belongs to.*/
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**Casts a vote for the option in the name of the user given.*/
    public function vote(User $user): void
    {
        $question=$this->question;
        //if ($question->hasVoted($user)) throw new \Exception("user has already voted"); //had to take this out because of multi-option questions
        if (!$question->isOpen()) {
            throw new \Exception("question not open");
        }
        QuestionUser::create([
            'question_id' => $question->id,
            'user_id' => $user->id,
        ]);
        $this->increment('votes');
    }
}
