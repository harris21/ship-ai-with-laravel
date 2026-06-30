<?php

use App\Ai\Agents\TicketClassifier;
use App\Models\User;

test('authenticated user can submit a ticket and queue classification', function () {
    TicketClassifier::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/tickets', [
        'subject' => 'Where is my order?',
        'body' => 'My package has not arrived and it has been two weeks.',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['ticket_id', 'status', 'message']);

    $this->assertDatabaseHas('tickets', [
        'user_id' => $user->id,
        'subject' => 'Where is my order?',
        'body' => 'My package has not arrived and it has been two weeks.',
        'status' => 'pending_classification',
    ]);

    TicketClassifier::assertQueued(
        fn ($prompt) => str_contains($prompt->prompt, 'package has not arrived')
    );
});

test('ticket submission requires subject and body', function () {
    TicketClassifier::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/tickets', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['subject', 'body']);

    $this->assertDatabaseCount('tickets', 0);
    TicketClassifier::assertNeverQueued();
});

test('guests cannot submit tickets', function () {
    TicketClassifier::fake();

    $response = $this->postJson('/tickets', [
        'subject' => 'Hello',
        'body' => 'Anyone there?',
    ]);

    $response->assertUnauthorized();
    $this->assertDatabaseCount('tickets', 0);
    TicketClassifier::assertNeverQueued();
});
