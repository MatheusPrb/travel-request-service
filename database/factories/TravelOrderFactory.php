<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TravelOrderFactory extends Factory
{
    public function definition(): array
    {
        $departureDate = fake()->dateTimeBetween('now', '+1 year');
        $returnDate = fake()->dateTimeBetween($departureDate, '+2 years');

        return [
            'user_id' => User::factory(),
            'destination' => fake()->city().', '.fake()->country(),
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'status' => 'solicitado',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'aprovado',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelado',
        ]);
    }

    public function requested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'solicitado',
        ]);
    }
}
