<?php

namespace Database\Factories\Voting;

use App\Models\Voting\Sitting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voting\Question>
 */
class QuestionFactory extends Factory
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
            'max_options' => $this->faker->numberBetween(1, 3),
            'opened_at' => now()->addHours($this->faker->numberBetween(-3, -2)),
            'closed_at' => now()->addHours($this->faker->numberBetween(-1, 1))
        ];
    }
}
