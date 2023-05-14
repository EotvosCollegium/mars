<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LanguageExam>
 */
class LanguageExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'path' => $this->faker->url,
            'language' => $this->faker->randomElement(array_keys(config('app.alfonso_languages'))),
            'level' => $this->faker->randomElement(['A1', 'A2', 'B1', 'B2', 'C1', 'C2']),
            'type' => $this->faker->randomElement(['ECL', 'TOEFL', 'IELTS', 'Cambridge']),
            'date' => $this->faker->dateTimeBetween($startDate = '-10 years', $endDate = 'now')
        ];
    }
}
