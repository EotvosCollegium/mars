<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Question;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    public function vote(User $user, Question $question)
    {
        return $question->canVote($user);
    }
}
