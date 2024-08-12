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
        Schema::create('reservable_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['washing_machine', 'room']);
            $table->boolean('out_of_order')->default(false);
            $table->timestamps();
        });
        Schema::create('reservation_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('frequency'); // in days
            $table->date('last_day');
            $table->boolean('verified');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('default_item');
            $table->time('default_from');
            $table->time('default_until');
            $table->string('default_title')->nullable();
            $table->text('default_note')->nullable();
            $table->timestamps();

            $table->foreign('default_item')->references('id')->on('reservable_items')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');
        });
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservable_item_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->boolean('verified');
            $table->string('title')->nullable();
            $table->text('note')->nullable();
            $table->datetime('reserved_from');
            $table->datetime('reserved_until');
            $table->timestamps();

            $table->foreign('reservable_item_id')->references('id')->on('reservable_items')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->index(['reservable_item_id', 'reserved_from']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('reservation_groups');
        Schema::dropIfExists('reservable_items');
    }
};
