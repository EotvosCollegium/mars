<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('semester_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the semester setting
            $table->unsignedSmallInteger('semester_id')->nullable(); // Foreign key to semesters table
            $table->timestamps();

            // Add foreign key constraint
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('restrict');
        });

        DB::table('semester_settings')->insert([
            ['name' => 'global'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('semester_settings');
    }
};