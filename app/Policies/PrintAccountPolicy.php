<?php

namespace App\Policies;

use App\Models\PrintAccount;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrintAccountPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * True if the user can use his/her print account.
     */
    public function use(User $user)
    {
        return true;
    }

    public function handleAny(User $user)
    {
        return false;
    }

    public function view(User $user, PrintAccount $printAccount): bool
    {
        return $user->id === $printAccount->user_id;
    }

    public function modify(User $user): bool
    {
        return false;
    }
    /**
     * Determine whether the user can transfer balance from the print account.
     * @param User $user
     * @param PrintAccount $printAccount
     * @return bool
     */
    public function transferBalance(User $user, PrintAccount $printAccount): bool
    {
        return $user->id == $printAccount->user_id;
    }
}
