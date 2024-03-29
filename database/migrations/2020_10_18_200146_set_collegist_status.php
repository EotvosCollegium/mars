<?php

use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

class SetCollegistStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $collegists = User::collegists();
        foreach ($collegists as $collegist) {
            $extern_id = Role::getObjectIdByName(Role::COLLEGIST, 'extern');
            $collegist->roles()->detach(Role::firstWhere('name', Role::COLLEGIST)->id);
            $collegist->roles()->attach(Role::firstWhere('name', Role::COLLEGIST)->id, ['object_id' => $extern_id]);
            $collegist->setStatusFor(Semester::current(), SemesterStatus::ACTIVE);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
