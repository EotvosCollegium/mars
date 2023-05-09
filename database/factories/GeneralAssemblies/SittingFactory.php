<?php

namespace Database\Factories\GeneralAssemblies;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneralAssemblies\GeneralAssembly>
 */
class GeneralAssemblyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => $this->faker->realText($maxNbChars = 50),
            'opened_at' => now()->addHours($this->faker->numberBetween(-3, -2)),
            'closed_at' => now()->addHours($this->faker->numberBetween(-1, 1)),
        ];
    }
}
