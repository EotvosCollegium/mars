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
        Schema::rename('printers', 'printer_configurations');
        Schema::table('printer_configurations', function ($table) {
            $table->renameColumn('name', 'description_hun')->unique(false)->change();
            $table->dropColumn('ip');
            $table->dropColumn('port');
            $table->string('description_eng')->after('description_hun');
            $table->string('lp_flags');
            $table->integer('one_sided_cost')->default(10);
            $table->integer('two_sided_cost')->nullable();
            $table->boolean('active')->default(false);
        });
        Schema::table('print_jobs', function ($table) {
            $table->renameColumn('printer_id', 'printer_configuration_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_jobs', function ($table) {
            $table->renameColumn('printer_configuration_id', 'printer_id');
        });
        Schema::table('printer_configurations', function ($table) {
            $table->dropColumn('active');
            $table->dropColumn('two_sided_cost');
            $table->dropColumn('one_sided_cost');
            $table->dropColumn('lp_flags');
            $table->dropColumn('description_eng');
            $table->renameColumn('description_hun', 'name')->unique(true)->change();
            $table->string('ip')->nullable();
            $table->string('port')->nullable();
        });
        Schema::rename('printer_configurations', 'printers');
    }
};
