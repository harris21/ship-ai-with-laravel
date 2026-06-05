<?php

namespace App\Ai\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;

class TrackCosts
{
    public function handle(AgentPrompt $prompt, Closure $next)
    {
        return $next($prompt)->then(function (AgentResponse $response) use ($prompt) {
            Log::info([
                'user_id' => auth()->id(),
                'agent' => get_class($prompt->agent),
                'provider' => $response->meta->provider,
                'model' => $response->meta->model,
                'input_tokens' => $response->usage->promptTokens,
                'output_tokens' => $response->usage->completionTokens,
            ]);
        });
    }
}
