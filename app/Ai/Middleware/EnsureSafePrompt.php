<?php

namespace App\Ai\Middleware;

use App\Ai\Agents\PromptGuard;
use Closure;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Prompts\AgentPrompt;

class EnsureSafePrompt
{
    public function handle(AgentPrompt $prompt, Closure $next)
    {
        $guard = new PromptGuard;

        $result = $guard->prompt($prompt->prompt);

        if (! $result['safe']) {
            Log::warning('Blocked unsafe prompt', [
                'category' => $result['category'],
                'reason' => $result['reason'],
                'user_id' => auth()->id(),
                'prompt' => str($prompt->prompt)->limit(200),
            ]);

            throw new \RuntimeException(
                'Your message could not be processed. Please rephrase your question about orders, shipping, returns, or account issues.'
            );
        }

        return $next($prompt);
    }
}
