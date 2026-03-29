<?php

namespace App\Http\Controllers;

use App\Ai\Agents\SupportAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'nullable|string',
        ]);

        $agent = new SupportAgent;

        if ($request->conversation_id) {
            $response = $agent
                ->continue($request->conversation_id, as: $request->user())
                ->prompt($request->message);
        } else {
            $response = $agent
                ->forUser($request->user())
                ->prompt($request->message);
        }

        return response()->json([
            'reply' => $response->text,
            'conversation_id' => $response->conversationId,
        ]);
    }
}
