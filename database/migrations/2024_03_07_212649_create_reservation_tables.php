<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservable_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 255);
            $table->enum('type', ['washing_machine', 'room']);
            // the default duration (in minutes) for which the item can be reserved
            // or should this be the slot size for the UI?
            $table->unsignedSmallInteger('default_reservation_duration');
            // if true, one can only reserve slots with the length of default_reservation_duration
            $table->boolean('is_default_compulsory');
            // the possible starting dates' endings
            $table->set('allowed_starting_minutes', range(0,59));
            $table->datetime('out_of_order_from')->nullable();
            $table->datetime('out_of_order_until')->nullable();
            $table->timestamps();
        });
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservable_item_id');
            $table->unsignedBigInteger('user_id');
            // whether it is valid and verified
            // for washing machines, it's always true;
            // for rooms, it's true if the secretariat has verified the reservation
            $table->boolean('verified')->default(true);
            // it can be null – e.g. for washing, it is not very useful
            $table->string('title', length: 255)->nullable();
            $table->datetime('reserved_from');
            $table->datetime('reserved_until');
            $table->timestamps();
            $table->text('note')->nullable();

            $table->foreign('reservable_item_id')->references('id')->on('reservable_items')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
        Schema::dropIfExists('reservable_items');
        Schema::dropIfExists('reservations');
    }
};
