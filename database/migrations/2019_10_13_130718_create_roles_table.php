<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Adding roles
        foreach (['sys-admin', 'collegist', 'tenant', 'workshop-administrator', 'workshop-leader', 'application-committee', 'aggregated-application-committee', 'secretary', 'director', 'staff', 'printer', 'locale-admin', 'student-council', 'student-council-secretary', 'board-of-trustees-member', 'ethics-commissioner', 'alumni', 'receptionist'] as $role_name) {
            DB::table('roles')->insert(['name' => $role_name]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
