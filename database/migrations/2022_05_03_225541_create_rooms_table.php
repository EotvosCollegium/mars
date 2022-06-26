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
        Schema::table('personal_information', function (Blueprint $table) {
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
            ['name'=>'204', 'capacity'=>3]
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
