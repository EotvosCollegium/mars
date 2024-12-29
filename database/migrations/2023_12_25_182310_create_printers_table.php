<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('ip')->nullable();
            $table->string('port')->nullable();
            $table->timestamp('paper_out_at')->nullable();
        });


        // Create the default printer
        DB::table('printers')->insert([
            'name' => env('PRINTER_NAME'),
            'ip' => env('PRINTER_IP'),
            'port' => env('PRINTER_PORT'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('printers');
    }
};
