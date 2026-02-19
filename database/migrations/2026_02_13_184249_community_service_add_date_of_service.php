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
        Schema::table('community_services', function (Blueprint $table) {
            // The date when the service itself was performed (_not_ that of submission).
            // Is a text, so that a non-standard format can be given (like 'early February 2026').
            $table->text('date_of_service')->after('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('community_services', function (Blueprint $table) {
            $table->dropColumn('date_of_service');
        });
    }
};
