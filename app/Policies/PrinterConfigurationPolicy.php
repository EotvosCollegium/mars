<?php

namespace App\Policies;

use App\Models\PrinterConfiguration;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrinterConfigurationPolicy
{
    use HandlesAuthorization;

    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function reportError(User $user, PrinterConfiguration $configuration)
    {
        return $configuration->active;
    }

    public function use(User $user, PrinterConfiguration $configuration)
    {
        return $configuration->active;
    }

    public function manageActivation(User $user, PrinterConfiguration $configuration)
    {
        return false;
    }

    public function manageAnyActivation(User $user)
    {
        return false;
    }

    public function viewLpFlags(User $user)
    {
        return false;
    }
}
