<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Question;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    public function vote(User $user, Question $question): bool
    {
        return $question->canVote($user);
    }

    public function view_results(User $user, Question $question): bool
    {
        if ($question->isClosed()) return $user->can('viewAny', Sitting::class);
        else return $user->can('administer', Sitting::class);
    }
}
