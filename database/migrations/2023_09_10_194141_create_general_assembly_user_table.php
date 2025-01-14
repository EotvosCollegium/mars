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
        // The table is for the excused users of a general assembly
        Schema::create('general_assembly_user', function (Blueprint $table) {
            $table->id();
            $table->UnsignedBigInteger('general_assembly_id');
            $table->foreignId('user_id');
            $table->unique(['general_assembly_id', 'user_id']);
            $table->timestamps();

            $table->index(['user_id'], 'general_assembly_user_user_id_foreign');

            $table->foreign(
                'general_assembly_id',
                'c_general_assembly_user_general_assembly_id_foreign'
            )
                ->references('id')->on('general_assemblies')->cascadeOnDelete();
            $table->foreign(
                'user_id',
                'c_general_assembly_user_user_id_foreign'
            )
                ->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('general_assembly_user');
    }
};
