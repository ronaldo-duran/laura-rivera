<?php

namespace Database\Factories;

use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'reference' => Donation::newReference(),
            'amount_in_cents' => $this->faker->randomElement([20000, 50000, 100000]) * 100,
            'currency' => 'COP',
            'status' => 'CREATED',
            'full_name' => $this->faker->name(),
            'document_type' => 'CC',
            'document_number' => (string) $this->faker->numberBetween(10000000, 1999999999),
            'email' => $this->faker->safeEmail(),
            'phone' => '3'.$this->faker->numerify('#########'),
            'terms_accepted_at' => now(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => 'APPROVED', 'paid_at' => now(), 'payment_method_type' => 'PSE']);
    }
}
