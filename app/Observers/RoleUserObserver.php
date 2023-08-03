<?php

namespace App\Observers;

use App\Models\RoleUser;
use Illuminate\Support\Facades\Mail;

class RoleUserObserver
{
    /**
     * Handle the RoleUser "created" event.
     *
     * @param  \App\Models\RoleUser  $roleUser
     * @return void
     */
    public function created(RoleUser $roleUser)
    {
        if($roleUser->user->verified){
            Mail::to($roleUser->user)->queue(new \App\Mail\RoleAttached($roleUser->user->name, $roleUser->role->translatedName, $roleUser->translatedName));
        }
    }

    /**
     * Handle the RoleUser "deleted" event.
     *
     * @param  \App\Models\RoleUser  $roleUser
     * @return void
     */
    public function deleted(RoleUser $roleUser)
    {
        if($roleUser->user->verified){
            Mail::to($roleUser->user)->queue(new \App\Mail\RoleDetached($roleUser->user->name, $roleUser->role->translatedName, $roleUser->translatedName));
        }
    }
}
