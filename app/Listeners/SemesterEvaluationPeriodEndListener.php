<?php

namespace App\Listeners;

use App\Events\SemesterEvaluationPeriodEnd;
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
        $users = User::doesntHaveStatusFor($event->periodicEvent->semester->succ())->get();
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

        foreach ($users as $user) {
            try {
                //deactivate collegist, give them alumni role.
                DB::transaction(function () use ($user) {
                    RoleUser::withoutEvents(function () use ($user) {
                        $user->removeRole(Role::collegist());
                        $user->addRole(Role::alumni());
                    });
                    Mail::to($user)->queue(new StatusDeactivated($user->name));
                });
            } catch (\Exception $e) {
                Log::error('Error deactivating collegist: ' . $user->name . ' - ' . $e->getMessage());
            }
        }
    }
}
