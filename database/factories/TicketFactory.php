<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'category' => fake()->randomElement(['billing', 'shipping', 'product', 'account', 'returns', 'general']),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'sentiment' => fake()->randomElement(['happy', 'neutral', 'frustrated', 'angry']),
            'summary' => fake()->sentence(),
            'status' => 'classified',
        ];
    }
}
