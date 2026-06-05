<?php

use App\Ai\Tools\OrderLookup;
use App\Models\Order;
use App\Models\User;
use Laravel\Ai\Tools\Request;

test('order lookup returns order details', function () {
    $user = User::factory()->create(['name' => 'Jane Smith']);

    $order = Order::factory()->completed()->create([
        'user_id' => $user->id,
        'total' => 149.99,
    ]);

    $tool = new OrderLookup;

    $result = $tool->handle(new Request([
        'order_id' => $order->id,
    ]));

    $data = json_decode($result, true);

    expect($data)
        ->toHaveKey('status', 'completed')
        ->toHaveKey('total', 149.99)
        ->toHaveKey('customer_name', 'Jane Smith');
});

test('order lookup returns error for missing orders', function () {
    $tool = new OrderLookup;

    $result = $tool->handle(new Request([
        'order_id' => 99999,
    ]));

    expect($result)->toContain('not found');
});
