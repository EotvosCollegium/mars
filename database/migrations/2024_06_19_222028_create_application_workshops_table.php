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
        Schema::create('application_workshops', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('workshop_id')->references('id')->on('workshops');
            $table->foreignId('application_id')->references('id')->on('applications');
            $table->boolean('called_in')->default(false);
            $table->boolean('admitted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_workshops');
    }
};
