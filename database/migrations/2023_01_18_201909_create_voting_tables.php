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
        Schema::create('sittings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->datetime('opened_at');
            $table->datetime('closed_at')->nullable();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sitting_id')->onDelete('cascade');
            $table->string('title');
            $table->integer('max_options')->default(1);
            $table->char('passcode', 8);
            $table->datetime('opened_at')->nullable();
            $table->datetime('closed_at')->nullable();
        });
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->onDelete('cascade');
            $table->string('title');
            $table->integer('votes')->default(0);
        });
        Schema::create('question_user', function (Blueprint $table) {
            $table->foreignId('question_id')->onDelete('cascade');
            $table->foreignId('user_id')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['question_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sittings');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('question_user');
    }
};
