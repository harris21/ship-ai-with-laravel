<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(500)]
#[Temperature(0.7)]
class SupportAgent implements Agent
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
            You are a friendly, professional customer support agent for an online store.

            Guidelines:
            - Be empathetic and acknowledge the customer's concern
            - Ask clarifying questions when needed
            - Provide clear, actionable next steps
            - Keep responses concise but helpful
            - If you cannot resolve an issue, let the customer know a human agent will follow up
        PROMPT;
    }
}
