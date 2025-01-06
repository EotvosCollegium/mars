<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacultiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->text('name');
        });
        foreach (['Állam- és Jogtudományi Kar', 'Bárczi Gusztáv Gyógypedagógiai Kar', 'Bölcsészettudományi Kar', 'Informatikai Kar', 'Gazdaságtudományi Kar', 'Pedagógiai és Pszichológiai Kar', 'Tanító- és Óvóképző Kar', 'Társadalomtudományi Kar', 'Természettudományi Kar'] as $faculty_name) {
            DB::table('faculties')->insert(['name' => $faculty_name]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('faculties');
    }
}
