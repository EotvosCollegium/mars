<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('internet_accesses', function (Blueprint $table) {
            $table->dropColumn('auto_approved_mac_slots');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('internet_accesses', function (Blueprint $table) {
            $table->integer('auto_approved_mac_slots')->default(3);
        });
    }
};
