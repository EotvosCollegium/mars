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
        Schema::create('language_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_information_id');
            $table->string('path');
            $table->enum('language', ['en', 'la', 'fr', 'it', 'de', 'sp', 'gr', 'other']);
            $table->enum('level', ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'])->nullable();
            $table->string('type');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('language_exams');
    }
};
