<?php

use App\Models\Role;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRolesToNewRegulations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('roles')->updateOrInsert(
            ['name'=>'student-council-secretary'],
            ['has_workshops' => 0, 'has_objects' => 0]
        );
        DB::table('roles')->updateOrInsert(
            ['name'=>'student-council-secretary'],
            [ 'has_workshops' => 0, 'has_objects' => 0]
        );
        DB::table('role_objects')->where('name', 'vice-president')->delete();
        DB::table('role_objects')->where('name', 'economic-leader')->delete();
        DB::table('role_objects')->where('name', 'science-leader')->delete();
        DB::table('role_objects')->where('name', 'economic-member')->delete();
        DB::table('role_objects')->where('name', 'science-member')->delete();
        DB::table('role_objects')->updateOrInsert(['role_id'=> Role::firstWhere('name', 'student-council')->id, 'name' => 'science-vice-president'], []);
        DB::table('role_objects')->updateOrInsert(['role_id'=> Role::firstWhere('name', 'student-council')->id, 'name' => 'economic-vice-president'], []);
        DB::table('role_objects')->updateOrInsert(['role_id'=> Role::firstWhere('name', 'student-council')->id, 'name' => 'cultural-referent'], []);
        DB::table('role_objects')->updateOrInsert(['role_id'=> Role::firstWhere('name', 'student-council')->id, 'name' => 'community-referent'], []);
        DB::table('role_objects')->updateOrInsert(['role_id'=> Role::firstWhere('name', 'student-council')->id, 'name' => 'communication-referent'], []);
        DB::table('role_objects')->updateOrInsert(['role_id'=> Role::firstWhere('name', 'student-council')->id, 'name' => 'sport-referent'], []);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
