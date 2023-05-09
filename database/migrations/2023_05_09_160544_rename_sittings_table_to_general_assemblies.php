<?php

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
        Schema::rename('sittings', 'general_assemblies');
        if(Schema::hasColumn('questions', 'sitting_id')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->renameColumn('sitting_id', 'general_assembly_id');
            });
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('general_assemblies', 'sittings');
    }
};
