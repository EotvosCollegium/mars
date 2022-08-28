<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePersonalInformationToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('personal_information', function (Blueprint $table) {
            $table->text('place_of_birth')->nullable()->change();
            $table->date('date_of_birth')->nullable()->change();
            $table->text('mothers_name')->nullable()->change();
            $table->text('country')->nullable()->change();
            $table->text('county')->nullable()->change();
            $table->text('zip_code')->nullable()->change();
            $table->text('city')->nullable()->change();
            $table->text('street_and_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('personal_information', function (Blueprint $table) {
            $table->text('place_of_birth')->change();
            $table->date('date_of_birth')->change();
            $table->text('mothers_name')->change();
            $table->text('country')->change();
            $table->text('county')->change();
            $table->text('zip_code')->change();
            $table->text('city')->change();
            $table->text('street_and_number')->change();
        });
    }
}
