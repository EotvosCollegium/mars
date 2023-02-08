<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Voting\Question;
use App\Models\Voting\Sitting;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    /**
     * Whether a user can cast a vote in a certain question.
     * For this, the user has to be an active collegist, the question has to be open
     * and the user must not have voted in the question.
    */
    public function vote(User $user, Question $question): bool
    {
        return $question->isOpen() && $user->isCollegist() && $user->isActive() && !$question->hasVoted($user);
    }

    /**
     * Whether a user can view the results of a certain question.
     * If it is still open, only people authorized to manage sittings can do so.
    */
    public function viewResults(User $user, Question $question): bool
    {
        if ($question->isClosed()) {
            return $user->can('viewAny', Sitting::class);
        } else {
            return $user->can('administer', Sitting::class);
        }
    }
}
