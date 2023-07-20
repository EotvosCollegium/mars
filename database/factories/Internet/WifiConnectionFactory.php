<?php

namespace Database\Factories\Internet;

use App\Models\Internet\WifiConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

class WifiConnectionFactory extends Factory
{
    protected $model = WifiConnection::class;

    public function definition()
    {
        return [
            'ip' => $this->faker->unique()->ipv4,
            'mac_address' => $this->faker->macAddress,
            'wifi_username' => 'wifiuser'.$this->faker->numberBetween(1, 10),
            'lease_start' => $this->faker->dateTime(),
            'lease_end' => $this->faker->dateTime(),
            'radius_timestamp' => $this->faker->dateTime(),
            'note' => ''
        ];
    }
}
