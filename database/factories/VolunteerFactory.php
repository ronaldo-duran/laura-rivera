<?php

namespace Database\Factories;

use App\Models\Volunteer;
use Illuminate\Database\Eloquent\Factories\Factory;

class VolunteerFactory extends Factory
{
    protected $model = Volunteer::class;

    public function definition(): array
    {
        return [
            'document_type' => 'CC',
            'document_number' => (string) $this->faker->unique()->numberBetween(10000000, 1999999999),
            'first_names' => $this->faker->firstName(),
            'last_names' => $this->faker->lastName().' '.$this->faker->lastName(),
            'preferred_name' => null,
            'treatment' => $this->faker->randomElement(array_keys(config('laura.tratamientos'))),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '3'.$this->faker->numerify('#########'),
            'neighborhood' => $this->faker->randomElement(config('laura.barrios')),
            'help_type' => $this->faker->randomElement(config('laura.tipos_voluntariado')),
            'terms_accepted_at' => now(),
            'terms_version' => config('laura.terminos_version'),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => 'Mozilla/5.0',
        ];
    }
}
