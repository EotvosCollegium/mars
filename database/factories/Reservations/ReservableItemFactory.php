<?php

namespace Database\Factories\Reservations;

use Illuminate\Database\Eloquent\Factories\Factory;

use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservations\ReservableItem>
 */
class ReservableItemFactory extends Factory
{
    /**
     * Define the model's default state.
     * Note: this only generates rooms,
     * as we create the washing machines in the seeder.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->realText(10),
            'type' => \App\Enums\ReservableItemType::ROOM,
            'out_of_order' => $this->faker->boolean(30)
        ];
    }
}
