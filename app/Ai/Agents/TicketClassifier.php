<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(300)]
#[Temperature(0.3)]
class TicketClassifier implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'PROMPT'
                You are a support ticket classifier for an online store.
                Analyze the customer's message and classify it accurately.

                Categories: billing, shipping, product, account, returns, general
                Priority levels: low, medium, high, urgent
                Sentiment options: happy, neutral, frustrated, angry
            PROMPT;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'category' => $schema->string()
                ->enum(['billing', 'shipping', 'product', 'account', 'returns', 'general'])
                ->description('The primary category of the support ticket')
                ->required(),
            'priority' => $schema->string()
                ->enum(['low', 'medium', 'high', 'urgent'])
                ->description('How urgent this ticket is')
                ->required(),
            'sentiment' => $schema->string()
                ->enum(['happy', 'neutral', 'frustrated', 'angry'])
                ->description('The emotional tone of the customer')
                ->required(),
            'summary' => $schema->string()
                ->description('A brief one-sentence summary of the issue')
                ->required(),
        ];
    }
}
