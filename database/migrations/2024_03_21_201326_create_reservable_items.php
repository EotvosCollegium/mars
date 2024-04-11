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
            $table->string('name');
            $table->enum('type', ['washing_machine', 'room']);
            // The default duration offered for a reservation (in minutes).
            $table->smallInteger('default_reservation_duration');
            $table->boolean('is_default_compulsory');
            // The minute endings of the possible starting times for a reservation.
            $table->set('allowed_starting_minutes', range(0, 59));
            $table->datetime('out_of_order_from')->nullable();
            $table->datetime('out_of_order_until')->nullable();
            $table->timestamps();
        });
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservable_item_id');
            $table->unsignedBigInteger('user_id');
            $table->string('title')->nullable();
            $table->string('note')->nullable();
            $table->datetime('reserved_from');
            $table->datetime('reserved_until');
            $table->timestamps();

            $table->foreign('reservable_item_id')->references('id')->on('reservable_items');
            $table->foreign('user_id')->references('id')->on('users');

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
