<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Checkout;
use App\Models\PaymentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'checkout_id' => Checkout::all()->random()->id,
            'semester_id' => \App\Models\Semester::all()->random()->id,
            'payment_type_id' => function (array $attributes) {
                return PaymentType::forCheckout(Checkout::findOrFail($attributes['checkout_id']))
                    ->random();
            },
            'receiver_id' => User::where('verified', true)->get()->random()->id,
            'payer_id' => User::where('verified', true)->get()->random()->id,
            'amount' => function (array $attributes) {
                $payment_type = PaymentType::findOrFail($attributes['payment_type_id']);
                return match ($payment_type->name) {
                    PaymentType::EXPENSE => round($this->faker->numberBetween(-100000, -1000), -3),
                    PaymentType::INCOME => round($this->faker->numberBetween(1000, 100000), -3),
                    PaymentType::KKT => config('custom.kkt'),
                    PaymentType::NETREG => config('custom.netreg'),
                    PaymentType::PRINT => round($this->faker->numberBetween(50, 1000), -1),
                    default => round($this->faker->numberBetween(1000, 100000), -3),
                };
            },
            'comment' => $this->faker->sentence,
            'moved_to_checkout' => ($this->faker->boolean)
                ? function (array $attributes) {
                    return \App\Models\Semester::findOrFail($attributes['semester_id'])
                        ->getStartDate()->addDays($this->faker->numberBetween(1, 100));
                }
        : null, //not in checkout
            'paid_at' => ($this->faker->boolean)
                ? function (array $attributes) {
                    return \App\Models\Semester::findOrFail($attributes['semester_id'])
                        ->getStartDate()->addDays($this->faker->numberBetween(1, 100));
                }
        : null, //not paid
            'created_at' => function (array $attributes) {
                return \App\Models\Semester::findOrFail($attributes['semester_id'])
                    ->getStartDate()->addDays($this->faker->numberBetween(1, 100));
            }
        ];
    }
}
