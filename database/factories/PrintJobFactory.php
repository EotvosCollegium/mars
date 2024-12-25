<?php

namespace Database\Factories;

use App\Enums\PrintJobStatus;
use App\Models\PrintJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrintJobFactory extends Factory
{
    protected $model = PrintJob::class;

    public function definition()
    {
        return [
            'printer_id' => 1,
            'filename' => $this->faker->text,
            'state' => $this->faker->randomElement(PrintJobStatus::cases()),
            'job_id' => $this->faker->randomNumber,
            'cost' => $this->faker->numberBetween(8, 1000),
        ];
    }
}
