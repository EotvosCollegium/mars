<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkshopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workshops', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->text('name');
        });
        foreach (['Angol-amerikai műhely', 'Biológia-kémia műhely', 'Bollók János Klasszika-filológia műhely', 'Filozófia műhely', 'Aurélien Sauvageot Francia műhely', 'Gazdaságtudományi műhely', 'Germanisztika műhely', 'Informatika műhely', 'Magyar műhely', 'Matematika-fizika műhely', 'Mendöl Tibor földrajz-, föld- és környezettudományi műhely', 'Olasz műhely', 'Orientalisztika műhely', 'Skandinavisztika műhely', 'Spanyol műhely', 'Szlavisztika műhely', 'Társadalomtudományi műhely', 'Történész műhely' ] as $workshop) {
            DB::table('workshops')->insert(['name' => $workshop]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('workshops');
    }
}
