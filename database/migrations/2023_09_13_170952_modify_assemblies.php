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
        // making opened_at nullable
        Schema::table('general_assemblies', function (Blueprint $table) {
            $table->datetime('opened_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('general_assemblies', function (Blueprint $table) {
            $table->datetime('opened_at')->nullable(false)->change();
        });
    }
};
