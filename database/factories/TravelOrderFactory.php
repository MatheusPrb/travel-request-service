<?php

namespace Database\Factories;

use App\Enums\TravelOrderStatus;
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
            'status' => TravelOrderStatus::REQUESTED->value,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelOrderStatus::APPROVED->value,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelOrderStatus::CANCELED->value,
        ]);
    }

    public function requested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelOrderStatus::REQUESTED->value,
        ]);
    }
}
