<?php

use App\Utils\DataCompresser;
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
        Schema::create('study_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_information_id');
            $table->string('name');
            $table->enum('type', ['bachelor', 'master', 'phd', 'ot', 'other'])->nullable();
            $table->unsignedBigInteger('start')->nullable();
            $table->unsignedBigInteger('end')->nullable();
            $table->timestamps();
        });

        foreach(DB::table('educational_information')->get() as $data) {
            foreach(DataCompresser::decompressData($data->program) as $program) {
                DB::table('study_lines')->insert(
                    [
                        'educational_information_id' => $data->id,
                        'name' => $program
                    ]
                );
            }
        }

        Schema::table('educational_information', function (Blueprint $table) {
            $table->dropColumn('program');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('study_lines');
    }
};
