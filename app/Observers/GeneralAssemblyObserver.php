<?php

namespace App\Observers;

use App\Models\GeneralAssemblies\GeneralAssembly;
use App\Models\Semester;
use App\Models\SemesterStatus;

class GeneralAssemblyObserver
{
    /**
     * Handle the GeneralAssembly "created" event.
     *
     * @param GeneralAssembly $generalAssembly
     * @return void
     */
    public function created(GeneralAssembly $generalAssembly): void
    {
        // Excuse passive students. They are excused at the creation of the general assembly (as opposed to when it
        // is opened) because the list of excused students is displayed as soon as the general assembly is created.
        $passiveUsers = Semester::current()->usersWithStatus(SemesterStatus::PASSIVE)->get();
        $generalAssembly->excusedUsers()->attach($passiveUsers, ['comment' => __('voting.automatically_excused_user_comment')]);
    }
}
