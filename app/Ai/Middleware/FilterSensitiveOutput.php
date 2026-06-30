<?php

namespace App\Ai\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;

class FilterSensitiveOutput
{
    protected array $patterns = [
        '/\b\d{3}-\d{2}-\d{4}\b/' => 'SSN',
        '/\b\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}\b/' => 'Credit Card',
        '/\bsk-[a-zA-Z0-9]{20,}\b/' => 'API Key',
    ];

    public function handle(AgentPrompt $prompt, Closure $next)
    {
        return $next($prompt)->then(function (AgentResponse $response) {
            foreach ($this->patterns as $pattern => $type) {
                if (preg_match($pattern, $response->text)) {
                    Log::critical('Sensitive data detected in AI response', [
                        'type' => $type,
                        'user_id' => auth()->id(),
                    ]);

                    $response->text = preg_replace($pattern, '[REDACTED]', $response->text);
                }
            }
        });
    }
}
