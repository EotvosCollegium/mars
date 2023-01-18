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
        Schema::create('sittings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->datetime('opened_at');
            $table->datetime('closed_at')->nullable();
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('sitting_id')
                ->references('id')->on('sittings')
                ->onDelete('cascade');
            $table->string('title');
            $table->datetime('opened_at')->nullable();
            $table->datetime('closed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
            $table->string('title');
            $table->integer('votes')->default(0);
            $table->timestamps();
        });
        Schema::create('question_user', function (Blueprint $table) {
            $table->foreignId('question_id')
                ->references('id')->on('questions')
                ->onDelete('cascade');
            $table->foreignId('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->datetime('voted_at');
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
        Schema::dropIfExists('sittings');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('options');
        Schema::dropIfExists('question_user');
    }
};
