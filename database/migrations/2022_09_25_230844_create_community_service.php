<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunityService extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('community_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('approver_id')->references('id')->on('users')->onDelete('cascade');
            $table->text('description');
            $table->boolean('approved')->nullable();
            $table->unsignedSmallInteger('semester_id');
            $table->timestamps();

            $table->foreign('semester_id')->references('id')->on('semesters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('community_services');
    }
}
