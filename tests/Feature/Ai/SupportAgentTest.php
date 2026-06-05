<?php

use App\Ai\Agents\SupportAgent;
use App\Ai\Agents\TicketClassifier;
use App\Models\User;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\QueuedAgentPrompt;

test('support agent receives customer messages', function () {
    SupportAgent::fake([
        'I can help you with that! Let me look up your order.',
    ]);

    $user = User::factory()->create();
    $agent = new SupportAgent;

    $response = $agent->forUser($user)->prompt('I need help with my order #1042');

    expect($response->text)->toBe('I can help you with that! Let me look up your order.');

    SupportAgent::assertPrompted(
        fn (AgentPrompt $prompt) => $prompt->contains('order #1042')
    );
});

test('support agent receives customer messages with context', function () {
    SupportAgent::fake([
        'Let me check your shipping status.',
    ]);

    $user = User::factory()->create();
    $agent = new SupportAgent;

    $agent->forUser($user)->prompt('Where is my package?');

    SupportAgent::assertPrompted(
        fn (AgentPrompt $prompt) => $prompt->contains('package')
    );
});

test('no unexpected AI calls are made during ticket creation', function () {
    SupportAgent::fake()->preventStrayPrompts();
    TicketClassifier::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/tickets', [
        'subject' => 'Broken widget',
        'body' => 'My widget stopped working yesterday.',
    ]);

    $response->assertStatus(201);

    SupportAgent::assertNeverPrompted();
});

test('ticket classification is queued on submission', function () {
    TicketClassifier::fake([
        json_encode([
            'category' => 'product',
            'priority' => 'medium',
            'sentiment' => 'frustrated',
            'summary' => 'Customer reports broken widget.',
        ]),
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/tickets', [
        'subject' => 'Broken widget',
        'body' => 'My widget stopped working yesterday after the update.',
    ]);

    $response->assertStatus(201);

    TicketClassifier::assertQueued(
        fn (QueuedAgentPrompt $prompt) => str_contains($prompt->prompt, 'widget stopped working')
    );
});

test('ticket classification is not queued for empty submissions', function () {
    TicketClassifier::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/tickets', [
        'subject' => '',
        'body' => '',
    ]);

    $response->assertStatus(422);

    TicketClassifier::assertNeverQueued();
});
