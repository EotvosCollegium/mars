<?php

namespace Database\Factories;

use App\Models\GeneralAssemblies\GeneralAssembly;
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
        return [
            'title' => $this->faker->realText($maxNbChars = 50),
            'max_options' => $this->faker->numberBetween(1, 3),
            'opened_at' => now()->addHours($this->faker->numberBetween(-3, -2)),
            'closed_at' => now()->addHours($this->faker->numberBetween(-1, 1)),
            // this will create questions for general assemblies,
            // where long answers are not permitted
            'has_long_answers' => false
        ];
    }
}
