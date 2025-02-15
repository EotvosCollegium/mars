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
        Schema::rename("printing_free_pages", "printing_free_printing_credits");
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->renameColumn('used_free_pages', 'used_free_printing_credits');
        });
        Schema::table('print_account_history', function (Blueprint $table) {
            $table->renameColumn('free_page_change', 'free_printing_credits_change');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_account_history', function (Blueprint $table) {
            $table->renameColumn('free_printing_credits_change', 'free_page_change');
        });
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->renameColumn('used_free_printing_credits', 'used_free_pages');
        });
        Schema::rename("printing_free_printing_credits", "printing_free_pages");
    }
};
