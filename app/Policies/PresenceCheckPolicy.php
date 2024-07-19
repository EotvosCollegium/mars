<?php

namespace App\Policies;

use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\GeneralAssemblies\PresenceCheck;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PresenceCheckPolicy
{
    use HandlesAuthorization;

    /**
     * Whether a user can cast a vote in a certain question.
     * For this, the user has to be an active collegist, the question has to be open
     * and the user must not have voted in the question.
    */
    public function signPresence(User $user, PresenceCheck $presence): bool
    {
        return $presence->isOpen() && $user->isCollegist(alumni: false) && $user->isActive() && !$presence->signedPresence($user);
    }

    /**
     * Whether a user can view the results of a certain question.
     * If it is still open, only people authorized to manage general_assemblies can do so.
    */
    public function viewResults(User $user, PresenceCheck $presence): bool
    {
        if ($presence->isClosed()) {
            return $user->can('viewAny', GeneralAssembly::class);
        } else {
            return $user->can('administer', GeneralAssembly::class);
        }
    }
}
