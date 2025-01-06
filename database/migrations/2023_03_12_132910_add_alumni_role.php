<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('roles')->where('name', 'alumni')->doesntExist()) {
            DB::table('roles')->insert([
                'name' => 'alumni',
                'has_workshops' => false,
                'has_objects' => false
            ]);
        }

        $collegist_role_id = DB::table('roles')->where('name', 'collegist')->first()->id;
        $alumni_role_id = DB::table('roles')->where('name', 'alumni')->first()->id;

        foreach(DB::table('semester_status')->where('status', 'DEACTIVATED')->get() as $semesterStatus) {
            DB::table('role_users')->where('user_id', $semesterStatus->user_id)->where('role_id', $collegist_role_id)->delete();
            DB::table('role_users')->insert([
                'user_id' => $semesterStatus->user_id,
                'role_id' => $alumni_role_id
            ]);
        }

        DB::table('semester_status')->where('status', 'PENDING')->orWhere('status', 'INACTIVE')->update(['status' => 'PASSIVE']);
        DB::table('semester_status')->where('status', 'DEACTIVATED')->delete();
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
};
