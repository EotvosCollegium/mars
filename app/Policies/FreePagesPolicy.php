<?php

namespace App\Policies;

use App\Models\FreePages;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FreePagesPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function create(User $user)
    {
        return false;
    }

    public function view(User $user, FreePages $freePages): bool
    {
        return $freePages->user_id == $user->id;
    }

    public function viewSelf(User $user): bool
    {
        return true;
    }

    public function viewAny(User $user)
    {
        return false;
    }

    public function update(User $user, FreePages $freePages): bool
    {
        return $freePages->user_id == $user->id;
    }
}
