<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename("configurable_texts", "configurable_values");
        Schema::table('configurable_values', function ($table) {
            $table->dropColumn('user_id');
            $table->dropColumn('summarize');
            $table->renameColumn('rawtext', 'raw_value');
            $table->enum('value_type', ['text', 'number'])->default('text')->after('raw_value');

            $table->unique(['key', 'workshop_id']);
        });

        DB::table('configurable_values')->insert([
            'key' => 'TOTAL_KKT_RESIDENT',
            'value_type' => 'number',
            'raw_value' => '4000',
        ]);

        DB::table('configurable_values')->insert([
            'key' => 'TOTAL_KKT_EXTERN',
            'value_type' => 'number',
            'raw_value' => '3000',
        ]);

        DB::table('configurable_values')->insert([
            'key' => 'WORKSHOP_BALANCE_RESIDENT',
            'value_type' => 'number',
            'raw_value' => '1000',
        ]);

        DB::table('configurable_values')->insert([
            'key' => 'WORKSHOP_BALANCE_EXTERN',
            'value_type' => 'number',
            'raw_value' => '1000',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('configurable_values')->where('value_type', 'number')->delete();
        Schema::table('configurable_values', function ($table) {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('summarize')->storedAs("CONCAT(`key`, ';', COALESCE(`workshop_id`, ''))");
            $table->renameColumn('raw_value', 'rawtext');
            $table->dropColumn('value_type');
        });
        Schema::rename("configurable_values", "configurable_texts");
    }
};
