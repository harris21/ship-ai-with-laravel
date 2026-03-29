<?php

namespace App\Ai\Tools;

use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

use function GuzzleHttp\json_encode;

class OrderLookup implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Look up an order by its order number. Returns order status, total, items and shipping information.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $order = Order::with('user')->find($request['order_id']);

        if (! $order) {
            return 'Order not found.';
        }

        return json_encode([
            'order_id' => $order->id,
            'status' => $order->status,
            'total' => $order->total,
            'placed_at' => $order->created_at->toDateString(),
            'customer_name' => $order->user->name,
        ]);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'order_id' => $schema->integer()
                ->description('The order number to look up')
                ->required(),
        ];
    }
}
