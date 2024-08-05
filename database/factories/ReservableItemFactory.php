<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReservableItem>
 */
class ReservableItemFactory extends Factory
{
    const DEFAULT_RESERVATION_DURATIONS = [15, 30, 45, 60, 90, 120, 180];

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
            'type' => 'room',
            'default_reservation_duration' => ReservableItemFactory::DEFAULT_RESERVATION_DURATIONS[random_int(0, count(ReservableItemFactory::DEFAULT_RESERVATION_DURATIONS)-1)],
            'is_default_compulsory' => false,
            'allowed_starting_minutes' => '0,15,30,45',
            'out_of_order' => $this->faker->boolean(30)
        ];
    }
}
