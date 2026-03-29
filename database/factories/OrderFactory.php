<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => fake()->randomElement(['processing', 'shipped', 'delivered', 'cancelled']),
            'total' => fake()->randomFloat(2, 15, 250),
        ];
    }

    public function processing(): static
    {
        return $this->state(['status' => 'processing']);
    }

    public function shipped(): static
    {
        return $this->state(['status' => 'shipped']);
    }

    public function delivered(): static
    {
        return $this->state(['status' => 'delivered']);
    }

    public function completed(): static
    {
        return $this->delivered();
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }
}
