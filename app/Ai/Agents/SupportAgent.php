<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CustomerHistory;
use App\Ai\Tools\OrderLookup;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxSteps(5)]
#[MaxTokens(500)]
#[Temperature(0.7)]
class SupportAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

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
                 - When a customer mentions an order number, use the OrderLookup tool to get real data
                 - When a customer provides their email, use the CustomerHistory tool for context
             PROMPT;
    }

    public function tools(): iterable
    {
        return [
            new OrderLookup,
            new CustomerHistory,
        ];
    }
}
