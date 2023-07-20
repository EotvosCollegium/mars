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
        Schema::table('wifi_connections', function (Blueprint $table) {
            $table->datetime('lease_start')->nullable();
            $table->datetime('lease_end')->nullable();
            $table->datetime('radius_timestamp')->nullable();
            $table->string('note');
        });

        Schema::table('internet_accesses', function (Blueprint $table) {
            $table->dropColumn('wifi_connection_limit');
        });

        Schema::table('mac_addresses', function (Blueprint $table) {
            $table->dropColumn('ip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wifi_connections', function (Blueprint $table) {
            $table->dropColumn('lease_start');
            $table->dropColumn('lease_end');
            $table->dropColumn('radius_timestamp');
            $table->dropColumn('note');
        });

        Schema::table('internet_accesses', function (Blueprint $table) {
            $table->integer('wifi_connection_limit')->default(2);
        });

        Schema::table('mac_addresses', function (Blueprint $table) {
            $table->string('ip')->nullable();
        });
    }
};
