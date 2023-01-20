<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Question;
use App\Models\User;

class Option extends Model
{
    use HasFactory;

    public function vote(User $user): void {
        $question=$this->belongsTo(Question::class);
        if ($question->hasVoted($user->id)) throw new Exception("user has already voted");
        DB::table('question_user')->insert([
            'question_id' => $question->id,
            'user_id' => $user->id,
            'voted_at' => now()
        ]);
        $this->votes++;
    }
}
