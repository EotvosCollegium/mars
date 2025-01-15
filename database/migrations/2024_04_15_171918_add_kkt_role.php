<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('role_objects')->updateOrInsert(['role_id' => DB::table('roles')->where('name', 'student-council')->first()->id, 'name' => 'kkt-handler'], []);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('role_objects')->where('name', 'kkt-handler')->delete();
    }
};
