<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
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
        $sitting = $this->faker->randomElement(\App\Models\Sitting::whereNotNull('closed_at')->limit(10)->get());
        $opened_at = $sitting->closed_at->addMinutes($this->faker->numberBetween(-50, -30));//$this->faker->dateTime($min = $sitting->opened_at, $max=$sitting->closed_at);
        return [
            'sitting_id' => $sitting->id,
            'title' => $this->faker->realText($maxNbChars = 20),
            'opened_at' => $opened_at,
            'closed_at' => $opened_at->addMinutes($this->faker->numberBetween(5, 10)),
        ];
    }
}
