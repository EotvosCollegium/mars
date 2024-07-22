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
        Schema::table('application_forms', function (Blueprint $table) {
            $table->boolean('submitted')->after('status')->default(false);
            $table->boolean('applied_for_resident_status')->after('status')->default(false);
            $table->dropColumn('status');
        });
        Schema::rename("application_forms", "applications");

        Schema::table('files', function (Blueprint $table) {
            $table->renameColumn('application_form_id', 'application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename("applications", "application_forms");

        Schema::table('application_forms', function (Blueprint $table) {
            $table->string('status')->after('submitted');
            $table->dropColumn('submitted');
            $table->dropColumn('applied_for_resident_status');
        });
    }
};
