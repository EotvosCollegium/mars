<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Question;
use App\Models\User;

class QuestionOption extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $fillable = ['question_id', 'title', 'votes'];

    public function question(): Question {
        return $this->belongsTo(Question::class)->first();
    }

    public function vote(User $user): void {
        $question=$this->question();
        //if ($question->hasVoted($user)) throw new \Exception("user has already voted"); //had to take this out because of multi-option questions
        if (!$question->isOpen()) throw new \Exception("question not open");
        DB::table('question_user')->insert([
            'question_id' => $question->id,
            'user_id' => $user->id,
            //'voted_at' => now() //now this is done by the automatic timestamps
        ]);
        $this->votes++; $this->save();
    }
}
