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
        // We separately calculate the out_of_order times.
        $out_of_order_from = $this->faker->boolean(50) ? Carbon::now()->add(-2, 'hour') : null;
        // it might have just expired, expired an hour ago or only be to expire
        $out_of_order_until = (is_null($out_of_order_from) || $this->faker->boolean(50))
                            ? null
                            : Carbon::now()->add(rand(-10, 10), 'minute');

        return [
            'name' => $this->faker->realText(10),
            'type' => 'room',
            'default_reservation_duration'
                => ReservableItemFactory::DEFAULT_RESERVATION_DURATIONS[
                    rand(0,count(ReservableItemFactory::DEFAULT_RESERVATION_DURATIONS)-1)
                ],
            'is_default_compulsory' => false,
            'allowed_starting_minutes' => "0,15,30,45",
            'out_of_order_from' => $out_of_order_from,
            'out_of_order_until' => $out_of_order_until
        ];
    }
}
