<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Main demo user (owns order #1042)
        $john = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // Pad with orders so that #1042 lands on the right ID
        // Create orders 1-1041 for other users first
        $otherUsers = User::factory(3)->create();

        // Sarah - referenced in scripts by email
        $sarah = User::factory()->create([
            'name' => 'Sarah',
            'email' => 'sarah@example.com',
        ]);

        // Create bulk orders to reach ID 1042
        // We need 1041 orders before order #1042
        foreach ($otherUsers as $user) {
            Order::factory(200)->create(['user_id' => $user->id]);
        }

        // Sarah gets some orders
        Order::factory()->delivered()->create(['user_id' => $sarah->id, 'total' => 45.00]);
        Order::factory()->delivered()->create(['user_id' => $sarah->id, 'total' => 129.99]);
        Order::factory()->shipped()->create(['user_id' => $sarah->id, 'total' => 67.50]);
        Order::factory()->processing()->create(['user_id' => $sarah->id, 'total' => 89.99]);
        Order::factory()->delivered()->create(['user_id' => $sarah->id, 'total' => 34.99]);

        // John gets orders leading up to and including #1042
        Order::factory(count: 1042 - Order::count() - 1)->create(['user_id' => $john->id]);

        // Order #1042 - the key demo order referenced in scripts
        Order::factory()->shipped()->create([
            'user_id' => $john->id,
            'total' => 89.99,
            'created_at' => now()->subDays(3),
        ]);

        // A few more orders after #1042
        Order::factory(5)->create(['user_id' => $john->id]);
        Order::factory(3)->create(['user_id' => $sarah->id]);
    }
}
