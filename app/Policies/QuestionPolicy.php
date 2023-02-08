<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Voting\Question;
use App\Models\Voting\Sitting;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    public function vote(User $user, Question $question): bool
    {
        return $question->isOpen() && $user->isCollegist() && $user->isActive() && !$question->hasVoted($user);
    }

    public function viewResults(User $user, Question $question): bool
    {
        if ($question->isClosed()) {
            return $user->can('viewAny', Sitting::class);
        } else {
            return $user->can('administer', Sitting::class);
        }
    }
}
