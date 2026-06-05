<?php

namespace App\Ai\Middleware;

use Closure;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Ai\Prompts\AgentPrompt;

class RateLimiting
{
    public function handle(AgentPrompt $prompt, Closure $next)
    {
        $userId = auth()->id() ?? 'anonymous';
        $key = "ai-prompt:{$userId}";

        if (RateLimiter::tooManyAttempts($key, 10)) {
            throw new \RuntimeException(
                'Too many requests. Please wait a moment before sending another message.'
            );
        }

        RateLimiter::increment($key);

        return $next($prompt);
    }
}
