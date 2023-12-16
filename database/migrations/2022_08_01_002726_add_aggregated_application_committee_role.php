<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAggregatedApplicationCommitteeRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('roles')->where('name', 'aggregated-application-committee')->count() == 0) {
            DB::table('roles')->insert(['name' => 'aggregated-application-committee']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('roles')->where('name', 'aggregated-application-committee')->delete();
    }
}
