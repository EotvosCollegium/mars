<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('study_lines', function (Blueprint $table) {
            $table->string('training_code')->after('type')->nullable(); //It is nullable it was not mandatory, but it will be from now on
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('study_lines', function (Blueprint $table) {
            $table->dropColumn('training_code');
        });
    }
};
