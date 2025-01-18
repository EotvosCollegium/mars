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
        Schema::table('periodic_events', function (Blueprint $table) {
            $table->renameColumn('event_model', 'event_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periodic_events', function (Blueprint $table) {
            $table->renameColumn('event_name', 'event_model');
        });
    }
};
