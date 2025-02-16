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
        DB::table('printing_free_printing_credits')
            ->update(array(
                'amount' => DB::raw('amount * ' . env('PRINT_COST_ONESIDED', '8')),
            ));
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->renameColumn('used_free_pages', 'used_free_printing_credits');
        });
        DB::table('print_jobs')
            ->update(array(
                'used_free_printing_credits' => DB::raw('used_free_printing_credits * ' . env('PRINT_COST_ONESIDED', '8')),
            ));
        Schema::table('print_account_history', function (Blueprint $table) {
            $table->renameColumn('free_page_change', 'free_printing_credits_change');
        });
        DB::table('print_account_history')
            ->update(array(
                'free_printing_credits_change' => DB::raw('free_printing_credits_change * ' . env('PRINT_COST_ONESIDED', '8')),
            ));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('print_account_history')
            ->update(array(
                'free_printing_credits_change' => DB::raw('free_printing_credits_change / ' . env('PRINT_COST_ONESIDED', '8')),
            ));
        Schema::table('print_account_history', function (Blueprint $table) {
            $table->renameColumn('free_printing_credits_change', 'free_page_change');
        });
        DB::table('print_jobs')
            ->update(array(
                'used_free_printing_credits' => DB::raw('used_free_printing_credits / ' . env('PRINT_COST_ONESIDED', '8')),
            ));
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->renameColumn('used_free_printing_credits', 'used_free_pages');
        });
        DB::table('printing_free_printing_credits')
            ->update(array(
                'amount' => DB::raw('amount / ' . env('PRINT_COST_ONESIDED', '8')),
            ));
        Schema::rename("printing_free_printing_credits", "printing_free_pages");
    }
};
