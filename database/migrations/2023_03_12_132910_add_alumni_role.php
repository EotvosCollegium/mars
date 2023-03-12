<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(DB::table('roles')->where('name', 'alumni')->doesntExist())
        {
            DB::table('roles')->insert([
                'name' => 'alumni',
                'has_workshops' => false,
                'has_objects' => false
            ]);
        }
        DB::table('semester_status')->whereIn('status', ['DEACTIVATED', 'PENDING', 'INACTIVE'])->delete();
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
