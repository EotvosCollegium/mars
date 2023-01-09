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
        if (DB::table('workshops')->where('name', \App\Models\Workshop::GAZDALKODASTUDOMANYI)->doesntExist())
            DB::table('workshops')->insert(['name' => \App\Models\Workshop::GAZDALKODASTUDOMANYI]);
        if (DB::table('faculties')->where('name', \App\Models\Faculty::GTI)->doesntExist())
            DB::table('faculties')->insert(['name' => \App\Models\Faculty::GTI]);
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
