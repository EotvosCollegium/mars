<?php

use App\Models\EducationalInformation;
use App\Models\Semester;
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
            $table->foreignIdFor(EducationalInformation::class);
            $table->string('name');
            $table->enum('type', ['bachelor', 'master', 'phd', 'ot', 'other'])->nullable();
            $table->foreignIdFor(Semester::class, 'start')->nullable();
            $table->foreignIdFor(Semester::class, 'end')->nullable();
            $table->timestamps();
        });


        foreach(EducationalInformation::all() as $data) {
            foreach(DataCompresser::decompressData($data->program) as $program) {
                $data->studyLines()->create([
                    'name' => $program
                ]);
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
