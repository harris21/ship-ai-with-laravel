<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TicketClassifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        $ticket = $request->user()->tickets()->create([
            'subject' => $request->subject,
            'body' => $request->body,
            'status' => 'pending_classification',
        ]);

        (new TicketClassifier)
            ->queue(prompt: $request->body, provider: config('ai.support_agent.providers'))
            ->then(function ($response) use ($ticket) {
                $ticket->update([
                    'category' => $response['category'],
                    'priority' => $response['priority'],
                    'sentiment' => $response['sentiment'],
                    'summary' => $response['summary'],
                    'status' => 'classified',
                ]);
            });

        return response()->json([
            'ticket_id' => $ticket->id,
            'status' => 'received',
            'message' => 'Your ticket has been submitted and will be reviewed shortly.',
        ], 201);
    }
}
