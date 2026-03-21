<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;

class ResetForRecording extends Command
{
    protected $signature = 'app:reset {--episode= : Episode number to reset for (records starting point)}';

    protected $description = 'Reset database and seed data for recording a specific episode';

    public function handle(): void
    {
        $episode = (int) $this->option('episode') ?: 0;

        $this->warn("Resetting for Episode {$episode} recording (starting point = episode ".max(0, $episode - 1).' end state)');

        // Fresh migration
        $this->info('Running migrate:fresh...');
        $this->call('migrate:fresh', ['--force' => true]);

        // Seed users + orders (available from Ep 3 onward, but safe to run always)
        if ($episode >= 3) {
            $this->info('Seeding users and orders...');
            $this->call('db:seed', ['--force' => true]);

            // Reassign order #1042 to the recording user
            $user = User::factory()->create([
                'name' => 'Harris Rafto',
                'email' => 'support-ai@test.com',
                'password' => bcrypt('password'),
            ]);

            $order = Order::find(1042);
            if ($order) {
                $order->update(['user_id' => $user->id]);
                Order::factory()->shipped()->create(['user_id' => $user->id, 'total' => 59.99]);
                Order::factory()->delivered()->create(['user_id' => $user->id, 'total' => 124.50]);
                Order::factory()->processing()->create(['user_id' => $user->id, 'total' => 34.99]);
                $this->info("Order #1042 assigned to {$user->email} ({$user->id})");
            }
        } else {
            // Still create the recording user for login
            User::factory()->create([
                'name' => 'Harris Rafto',
                'email' => 'support-ai@test.com',
                'password' => bcrypt('password'),
            ]);
            $this->info('Created recording user: support-ai@test.com / password');
        }

        // Seed knowledge base (available from Ep 5 onward)
        if ($episode >= 5) {
            $this->info('Seeding knowledge base...');
            $this->call('kb:seed');
        }

        // Vector store note (Ep 6+)
        if ($episode >= 6) {
            $this->warn('Note: Vector store (VECTOR_STORE_ID) is already configured in .env. No need to re-run kb:setup-store.');
        }

        $this->newLine();
        $this->info('Ready to record Episode '.$episode);
        $this->info('Login: support-ai@test.com / password');

        if ($episode >= 3) {
            $this->info('Order #1042: shipped, $89.99, belongs to your account');
        }
    }
}
