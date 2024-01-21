<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->string('name')->unique();
            $table->integer('capacity');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('room')->nullable();
            $table->foreign('room')->references('name')->on('rooms');
        });

        DB::table('rooms')->insert([
            ['name'=>'235', 'capacity'=>3],
            ['name'=>'234', 'capacity'=>3],
            ['name'=>'233', 'capacity'=>3],
            ['name'=>'232', 'capacity'=>3],
            ['name'=>'231', 'capacity'=>3],
            ['name'=>'230', 'capacity'=>3],
            ['name'=>'229', 'capacity'=>3],
            ['name'=>'228', 'capacity'=>3],
            ['name'=>'227', 'capacity'=>3],
            ['name'=>'226', 'capacity'=>3],
            ['name'=>'225', 'capacity'=>3],
            ['name'=>'224', 'capacity'=>3],
            ['name'=>'223', 'capacity'=>3],
            ['name'=>'222', 'capacity'=>3],
            ['name'=>'221', 'capacity'=>3],
            ['name'=>'220', 'capacity'=>3],
            ['name'=>'219', 'capacity'=>3],
            ['name'=>'218', 'capacity'=>3],
            ['name'=>'217', 'capacity'=>2],
            ['name'=>'216', 'capacity'=>2],
            ['name'=>'215', 'capacity'=>2],
            ['name'=>'214', 'capacity'=>3],
            ['name'=>'213', 'capacity'=>3],
            ['name'=>'212', 'capacity'=>3],
            ['name'=>'211', 'capacity'=>3],
            ['name'=>'210', 'capacity'=>3],
            ['name'=>'209', 'capacity'=>3],
            ['name'=>'208', 'capacity'=>3],
            ['name'=>'207', 'capacity'=>3],
            ['name'=>'206', 'capacity'=>3],
            ['name'=>'205A', 'capacity'=>3],
            ['name'=>'205B', 'capacity'=>3],
            ['name'=>'204', 'capacity'=>3],
            ['name'=>'327', 'capacity'=>3],
            ['name'=>'326', 'capacity'=>3],
            ['name'=>'325', 'capacity'=>3],
            ['name'=>'324', 'capacity'=>3],
            ['name'=>'323', 'capacity'=>3],
            ['name'=>'322', 'capacity'=>3],
            ['name'=>'321', 'capacity'=>3],
            ['name'=>'320', 'capacity'=>3],
            ['name'=>'319', 'capacity'=>3],
            ['name'=>'318', 'capacity'=>2],
            ['name'=>'317', 'capacity'=>3],
            ['name'=>'316', 'capacity'=>2],
            ['name'=>'315', 'capacity'=>2],
            ['name'=>'314', 'capacity'=>3],
            ['name'=>'313', 'capacity'=>2],
            ['name'=>'312', 'capacity'=>3],
            ['name'=>'311', 'capacity'=>3],
            ['name'=>'310', 'capacity'=>3],
            ['name'=>'309', 'capacity'=>3],
            ['name'=>'308', 'capacity'=>3],
            ['name'=>'307', 'capacity'=>3],
            ['name'=>'306', 'capacity'=>3],
            ['name'=>'305', 'capacity'=>3],
            ['name'=>'304', 'capacity'=>3],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
}
