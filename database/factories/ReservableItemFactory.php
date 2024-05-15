<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReservableItem>
 */
class ReservableItemFactory extends Factory
{
    // The possible default reservation (UI slot) sizes.
    private const DEFAULT_RESERVATION_DURATIONS = [5, 15, 20, 30, 60, 90, 120];

    /**
     * Define the model's default state.
     * Beware: this always defines a room, as washing machines are created manually.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->realText(10),
            'type' => 'room',
            'default_reservation_duration'
                => ReservableItemFactory::DEFAULT_RESERVATION_DURATIONS[
                    rand(0,count(ReservableItemFactory::DEFAULT_RESERVATION_DURATIONS)-1)
                ],
            'is_default_compulsory' => false,
            'allowed_starting_minutes' => "0,15,30,45",
        ];
    }
}
