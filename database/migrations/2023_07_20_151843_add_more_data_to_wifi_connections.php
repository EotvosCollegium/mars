<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wifi_connections', function (Blueprint $table) {
            $table->removeColumn('lease_start');
            $table->removeColumn('lease_end');
            $table->removeColumn('radius_timestamp');
            $table->removeColumn('note');
        });
    }
};
