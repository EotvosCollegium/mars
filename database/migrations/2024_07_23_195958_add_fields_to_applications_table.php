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
        Schema::table('applications', function (Blueprint $table) {
            $table->boolean('admitted_for_resident_status')->default(false)->after('applied_for_resident_status');
            $table->softDeletes();
        });
        Schema::table('application_workshops', function (Blueprint $table) {
            $table->dropForeign('application_workshops_application_id_foreign');
            $table->foreign('application_id')->references('id')->on('applications')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
