<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->foreignId('printer_id')->nullable()->after('user_id')->constrained();
            $table->boolean('used_free_pages')->default(false)->after('cost');
            $table->dropColumn('filepath');
        });
        DB::table('print_jobs')->update([
            'printer_id' => DB::table('printers')->first()->id
        ]);
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->foreignId('printer_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropForeign(['printer_id']);
            $table->dropColumn('printer_id');
            $table->dropColumn('used_free_pages');
            $table->string('filepath')->after('user_id');
        });
    }
};
