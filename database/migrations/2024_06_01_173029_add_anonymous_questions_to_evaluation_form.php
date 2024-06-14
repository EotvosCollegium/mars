<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('answer_sheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('semester_id');
            $table->unsignedSmallInteger('year_of_acceptance');
            // no timestamps (for better pseudonimity)
            // note: the order of records might still break anonymity

            $table->foreign('semester_id')->references('id')->on('semesters');
        });

        Schema::table('questions', function (Blueprint $table) {
            // for a polymorphic relationship
            $table->string('parent_type')->after('general_assembly_id')->nullable();
            $table->renameColumn('general_assembly_id', 'parent_id');
            $table->boolean('has_long_answers'); // whether it expects a long text answer
        });
        DB::table('questions')->update(['parent_type' => 'App\Models\GeneralAssemblies\GeneralAssembly']);
        // and then make it non-nullable
        Schema::table('questions', function (Blueprint $table) {
            $table->string('parent_type')->nullable(false)->change();
        });

        Schema::create('answer_sheet_question_option', function (Blueprint $table) {
            $table->unsignedBigInteger('answer_sheet_id');
            $table->unsignedBigInteger('question_option_id');
            // no timestamps

            $table->foreign('answer_sheet_id')->references('id')->on('answer_sheets');
            $table->foreign('question_option_id')->references('id')->on('question_options');
        });

        Schema::create('long_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('answer_sheet_id');
            $table->text('text');
            // no timestamps

            $table->foreign('question_id')->references('id')->on('questions');
            $table->foreign('answer_sheet_id')->references('id')->on('answer_sheets');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('long_answers');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('has_long_answers');
            $table->dropColumn('parent_type');
            $table->renameColumn('parent_id', 'general_assembly_id');
        });
    }
};
