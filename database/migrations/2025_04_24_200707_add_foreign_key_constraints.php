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
        Schema::table('epistola', function (Blueprint $table) {
            $table->foreign('uploader_id')->references('id')->on('users');
        });

        Schema::table('language_exams', function (Blueprint $table) {
            $table->foreign('educational_information_id')->references('id')->on('educational_information');
        });

        Schema::table('mr_and_miss_categories', function (Blueprint $table) {
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::table('printing_free_printing_credits', function (Blueprint $table) {
            $table->unsignedBigInteger('last_modified_by')->change();
            $table->foreign('last_modified_by')->references('id')->on('users');
        });

        Schema::table('print_accounts', function (Blueprint $table) {
            $table->foreign('last_modified_by')->references('id')->on('users');
        });

        Schema::table('print_account_history', function (Blueprint $table) {
            $table->foreign('modified_by')->references('id')->on('users');
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('questions');
        });

        Schema::table('semester_evaluations', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedSmallInteger('semester_id')->change();
            $table->foreign('semester_id')->references('id')->on('semesters');
        });

        Schema::table('study_lines', function (Blueprint $table) {
            $table->foreign('educational_information_id')->references('id')->on('educational_information');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('epistola', function (Blueprint $table) {
            $table->dropForeign(['uploader_id']);
        });

        Schema::table('language_exams', function (Blueprint $table) {
            $table->dropForeign(['educational_information_id']);
        });

        Schema::table('mr_and_miss_categories', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('printing_free_printing_credits', function (Blueprint $table) {
            $table->dropForeign(['last_modified_by']);
            $table->bigInteger('last_modified_by')->change();
        });

        Schema::table('print_accounts', function (Blueprint $table) {
            $table->dropForeign(['last_modified_by']);
        });

        Schema::table('print_account_history', function (Blueprint $table) {
            $table->dropForeign(['modified_by']);
        });

        Schema::table('question_options', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
        });

        Schema::table('semester_evaluations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['semester_id']);
            $table->unsignedBigInteger('semester_id')->change();
        });

        Schema::table('study_lines', function (Blueprint $table) {
            $table->dropForeign(['educational_information_id']);
        });
    }
};
