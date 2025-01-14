<?php

use Illuminate\Database\Migrations\Migration;

class SetMacToOne extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('internet_accesses')->update([
            'auto_approved_mac_slots' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
