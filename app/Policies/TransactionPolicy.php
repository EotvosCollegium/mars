<?php

namespace App\Policies;

use App\Models\Checkout;
use App\Models\PaymentType;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function delete(User $user, Transaction $transaction): bool
    {
        //print transaction should not be deleted as deleting won't change the user's print balance
        if ($transaction->type->name == PaymentType::PRINT) {
            return false;
        }

        if ($transaction->checkout->name == Checkout::admin()->name) {
            return $user->hasRole(Role::SYS_ADMIN);
        }

        if ($transaction->checkout->name == Checkout::studentsCouncil()->name) {
            return $user->hasRole(Role::STUDENT_COUNCIL, Role::ECONOMIC_LEADER)
                || ($user->hasRole(Role::STUDENT_COUNCIL, Role::ECONOMIC_MEMBER)
                    && $transaction->receiver_id == $user->id
                );
        }

        return false;
    }
}
