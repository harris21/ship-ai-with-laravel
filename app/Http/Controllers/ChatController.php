<?php

namespace App\Http\Controllers;

use App\Ai\Agents\SupportAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Streaming\Events\TextDelta;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'nullable|string',
        ]);

        $response = $this->resolveAgent($request)->prompt($request->message);

        return response()->json([
            'reply' => $response->text,
            'conversation_id' => $response->conversationId,
        ]);
    }

    public function stream(Request $request): StreamedResponse
    {
        $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'nullable|string',
        ]);

        try {
            $providers = config('ai.support_agent.providers');

            $streamable = $this->resolveAgent($request)
                ->stream($request->message, provider: $providers);

            $conversationId = null;

            $streamable->then(function ($response) use (&$conversationId) {
                $conversationId = $response->conversationId;
            });

            return response()->stream(function () use ($streamable, &$conversationId) {
                foreach ($streamable as $event) {
                    if ($event instanceof TextDelta) {
                        yield 'data: '.json_encode(['text' => $event->delta])."\n\n";
                    }
                }

                yield 'data: '.json_encode(['conversation_id' => $conversationId])."\n\n";
                yield "data: [DONE]\n\n";
            }, headers: ['Content-Type' => 'text/event-stream']);
        } catch (RuntimeException $e) {
            return response()->stream(function () use ($e) {
                yield 'data: '.json_encode(['text' => $e->getMessage()])."\n\n";
                yield "data: [DONE]\n\n";
            }, headers: ['Content-Type' => 'text/event-stream']);
        }
    }

    private function resolveAgent(Request $request): SupportAgent
    {
        $agent = new SupportAgent;

        if ($request->conversation_id) {
            return $agent->continue($request->conversation_id, as: $request->user());
        }

        return $agent->forUser($request->user());
    }
}
