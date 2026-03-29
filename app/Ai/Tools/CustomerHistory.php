<?php

namespace App\Ai\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CustomerHistory implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Look up a customer\s recent order history by their email address. Returns their last 5 orders.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $user = User::where('email', $request['email'])->first();

        if (! $user) {
            return 'Customer not found.';
        }

        $orders = $user->orders()
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($order) => [
                'order_id' => $order->id,
                'status' => $order->status,
                'total' => $order->total,
                'placed_at' => $order->created_at->toDateString(),
            ]);

        return json_encode([
            'customer_name' => $user->name,
            'email' => $user->email,
            'total_orders' => $user->orders()->count(),
            'recent_orders' => $orders,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'email' => $schema
                ->string()
                ->description('The customer\'s email address')
                ->required(),
        ];
    }
}
