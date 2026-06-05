<?php

use App\Ai\Agents\SupportAgent;
use App\Models\User;
use Laravel\Ai\Prompts\AgentPrompt;

test('authenticated users can send chat messages', function () {
    SupportAgent::fake([
        'Hello! How can I help you today?',
    ]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/chat', [
        'message' => 'Hi, I need some help',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['reply', 'conversation_id']);

    expect($response->json('reply'))
        ->toBe('Hello! How can I help you today?');

    SupportAgent::assertPrompted(
        fn (AgentPrompt $prompt) => $prompt->contains('I need some help')
    );
});

test('chat requires authentication', function () {
    SupportAgent::fake();

    $response = $this->postJson('/chat', [
        'message' => 'Hello',
    ]);

    $response->assertUnauthorized();

    SupportAgent::assertNeverPrompted();
});

test('chat validates the message field', function () {
    SupportAgent::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/chat', [
        'message' => '',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('message');

    SupportAgent::assertNeverPrompted();
});
