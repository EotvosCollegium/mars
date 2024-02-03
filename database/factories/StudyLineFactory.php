<?php

namespace Database\Factories;

use App\Models\Semester;
use App\Models\StudyLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudyLine>
 */
class StudyLineFactory extends Factory
{
    protected $model = StudyLine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'educational_information_id' => \App\Models\EducationalInformation::factory()->create()->id,
            'name' => $this->faker->jobTitle,
            'type' => $this->faker->randomElement(array_keys(\App\Models\StudyLine::TYPES)),
            'start' => Semester::current()->id,
        ];
    }
}
