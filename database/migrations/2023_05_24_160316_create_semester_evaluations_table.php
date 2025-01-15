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
        Schema::create('semester_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->unsignedBigInteger('semester_id');
            $table->text('alfonso_note')->nullable();
            $table->json('courses')->nullable();
            $table->text('courses_note')->nullable();
            $table->string('current_avg')->nullable();
            $table->string('last_avg')->nullable();
            $table->text('general_assembly_note')->nullable();
            $table->text('professional_results')->nullable();
            $table->text('research')->nullable();
            $table->text('publications')->nullable();
            $table->text('conferences')->nullable();
            $table->text('scholarships')->nullable();
            $table->text('educational_activity')->nullable();
            $table->text('public_life_activities')->nullable();
            $table->boolean('can_be_shared')->default(false);
            $table->text('feedback')->nullable();
            $table->boolean('resign_residency')->default(false);
            $table->string('next_status')->nullable();
            $table->string('next_status_note')->nullable();
            $table->boolean('will_write_request')->default(false);
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
        Schema::dropIfExists('semester_evaluations');
    }
};
