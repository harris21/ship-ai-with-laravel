<?php

namespace App\Ai\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;

class LogPrompts
{
    public function handle(AgentPrompt $prompt, Closure $next)
    {
        $startTime = microtime(true);

        Log::info('AI prompt sent', [
            'agent' => get_class($prompt->agent),
            'prompt' => str($prompt->prompt)->limit(200),
        ]);

        return $next($prompt)->then(function (AgentResponse $response) use ($prompt, $startTime) {
            $duration = round(microtime(true) - $startTime, 2);

            Log::info('AI response received', [
                'agent' => get_class($prompt->agent),
                'tokens_in' => $response->usage->promptTokens,
                'tokens_out' => $response->usage->completionTokens,
                'provider' => $response->meta->provider,
                'model' => $response->meta->model,
                'duration_seconds' => $duration,
            ]);
        });
    }
}
