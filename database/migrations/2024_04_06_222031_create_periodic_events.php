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
        Schema::dropIfExists('event_triggers');

        Schema::create('periodic_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_model');
            $table->unsignedSmallInteger('semester_id')->nullable()->references('id')->on('semesters');
            $table->dateTime('start_date');
            $table->dateTime('start_handled')->nullable();
            $table->dateTime('end_date');
            $table->datetime('extended_end_date')->nullable();
            $table->dateTime('end_handled')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodic_events');
    }
};
