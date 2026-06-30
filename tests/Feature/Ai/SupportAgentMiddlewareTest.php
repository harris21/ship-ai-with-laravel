<?php

use App\Ai\Agents\SupportAgent;
use App\Ai\Middleware\EnsureSafePrompt;
use App\Ai\Middleware\FilterSensitiveOutput;
use App\Ai\Middleware\LogPrompts;
use App\Ai\Middleware\RateLimiting;
use App\Ai\Middleware\TrackCosts;
use App\Models\User;

test('support agent registers the expected middleware', function () {
    expect((new SupportAgent)->middleware())->toBe([
        EnsureSafePrompt::class,
        RateLimiting::class,
        LogPrompts::class,
        TrackCosts::class,
        FilterSensitiveOutput::class,
    ]);
});

test('a prompt flows through the middleware chain without error', function () {
    SupportAgent::fake([
        'Sure, I can help you with that order.',
    ]);

    $user = User::factory()->create();

    $response = (new SupportAgent)->forUser($user)->prompt('I need help with my order #1042');

    expect($response->text)->toBe('Sure, I can help you with that order.');

    SupportAgent::assertPrompted(
        fn ($prompt) => str_contains($prompt->prompt, 'order #1042')
    );
});
