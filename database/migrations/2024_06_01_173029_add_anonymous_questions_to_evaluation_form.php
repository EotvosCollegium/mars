<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->renameColumn('general_assembly_id', 'parent_id');
            // for a polymorphic relationship
            $table->string('parent_type')->after('general_assembly_id')->nullable();
            $table->boolean('has_long_answers'); // whether it expects a long text answer
        });
        DB::table('questions')->update(['parent_type' => 'general_assembly']);
        // and then make it non-nullable
        Schema::table('questions', function (Blueprint $table) {
            $table->string('parent_type')->nullable(false)->change();
        });

        Schema::create('long_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->text('text');
            // $table->timestamps();   // no timestamps for better anonymity

            $table->foreign('question_id')->references('id')->on('questions');
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
