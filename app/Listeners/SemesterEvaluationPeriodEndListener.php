<?php

namespace App\Listeners;

use App\Events\SemesterEvaluationPeriodEnd;
use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Mail\EvaluationFormClosed;
use App\Mail\StatusDeactivated;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SemesterEvaluationPeriodEndListener
{
    /**
     * Handle the event.
     */
    public function handle(SemesterEvaluationPeriodEnd $event): void
    {
        // users who do not have status for the following semester did not fill the form
        $users = SemesterEvaluationController::usersHaventFilledOutTheForm($event->periodicEvent->semester);
        $users_names = $users->pluck('name')->toArray();

        if (User::secretary()) {
            Mail::to(User::secretary())->queue(new EvaluationFormClosed(User::secretary()->name, $users_names));
        }
        if (User::president()) {
            Mail::to(User::president())->queue(new EvaluationFormClosed(User::president()->name, $users_names));
        }
        if (User::director()) {
            Mail::to(User::director())->queue(new EvaluationFormClosed(User::director()->name, $users_names));
        }
        foreach (User::workshopLeaders() as $user) {
            Mail::to($user)->queue(new EvaluationFormClosed($user->name));
        }
    }
}
