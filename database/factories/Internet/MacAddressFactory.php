<?php

namespace Database\Factories\Internet;

use App\Models\Internet\MacAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class MacAddressFactory extends Factory
{
    protected $model = MacAddress::class;

    public function definition()
    {
        return [
            'mac_address' => $this->faker->macAddress,
            'comment' => $this->faker->text,
            'state' => $this->faker->randomElement(MacAddress::STATES),
        ];
    }
}
