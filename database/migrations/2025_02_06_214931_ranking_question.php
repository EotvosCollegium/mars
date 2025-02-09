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
        Schema::table('long_answers', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
            $table->dropForeign(['answer_sheet_id']);

            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->foreign('answer_sheet_id')->references('id')->on('answer_sheets')->onDelete('cascade');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->enum('question_type', ['selection', 'text_answer', 'ranking'])->default('selection');
            $table->integer('max_options')->nullable()->change();
            $table->text('results_cache')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        Schema::table('long_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('answer_sheet_id')->nullable()->change();
        });
        DB::table('questions')->where('has_long_answers', 1)->update(['question_type' => 'text_answer']);
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('has_long_answers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('questions')->whereNull('max_options')->update(['max_options' => 1]);
        Schema::table('questions', function (Blueprint $table) {
            $table->boolean('has_long_answers');
            $table->integer('max_options')->nullable(false)->change();
        });
        DB::table('questions')->where('question_type', 'text_answer')->update(['has_long_answers' => 1]);
        DB::table('questions')->whereNot(function (Illuminate\Database\Query\Builder $query) {
            $query->where('question_type', 'selection')
                  ->orWhere('question_type', 'text_answer');
        })->delete();
        Schema::table('long_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('answer_sheet_id')->nullable(false)->change();
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('question_type');
            $table->dropColumn('results_cache');
            $table->dropTimestamps();
            $table->dropSoftDeletes();
        });
        Schema::table('long_answers', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
            $table->dropForeign(['answer_sheet_id']);

            $table->foreign('question_id')->references('id')->on('questions')->onDelete('restrict');
            $table->foreign('answer_sheet_id')->references('id')->on('answer_sheets')->onDelete('restrict');
        });
    }
};
