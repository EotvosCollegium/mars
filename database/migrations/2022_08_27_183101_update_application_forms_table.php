<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateApplicationFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('application_forms', function (Blueprint $table) {
            $table->renameColumn('accommodation', 'present');
        });
        Schema::table('application_forms', function (Blueprint $table) {
            $table->boolean('accommodation')->default(false)->after('present');
        });
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
