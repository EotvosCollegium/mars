<?php

use Illuminate\Database\Migrations\Migration;

class AddWorkshop extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::table('workshops')->where('name', 'Gazdaságtudományi műhely')->doesntExist()) {
            DB::table('workshops')->insert(['name' => 'Gazdaságtudományi műhely']);
        }
        if (DB::table('faculties')->where('name', 'Gazdaságtudományi Kar')->doesntExist()) {
            DB::table('faculties')->insert(['name' => 'Gazdaságtudományi Kar']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nothing to do here
    }
}
