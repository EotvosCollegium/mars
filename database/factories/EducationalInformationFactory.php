<?php

namespace Database\Factories;

use App\Models\EducationalInformation;
use App\Models\StudyLine;
use Illuminate\Database\Eloquent\Factories\Factory;

class EducationalInformationFactory extends Factory
{
    protected $model = EducationalInformation::class;

    public function definition()
    {
        return [
            'year_of_graduation' => $this->faker->numberBetween($min = 2015, $max = date('Y')),
            'high_school' => $this->faker->company,
            'neptun' => $this->faker->regexify('[A-Z0-9]{6}'),
            'year_of_acceptance' => $this->faker->numberBetween($min = 2015, $max = date('Y')),
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
