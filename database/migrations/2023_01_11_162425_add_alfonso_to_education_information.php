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
        Schema::table('educational_information', function (Blueprint $table) {
            $table->enum('alfonso_language', ['en', 'fr', 'it', 'de', 'sp', 'gr', 'la'])->nullable();
            $table->enum('alfonso_desired_level', ['B2', 'C1'])->nullable();
            $table->date('alfonso_passed_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('education_information', function (Blueprint $table) {
            $table->dropColumn('alfonso_language');
            $table->dropColumn('alfonso_desired_level');
            $table->dropColumn('alfonso_passed_by');
        });
    }
};
