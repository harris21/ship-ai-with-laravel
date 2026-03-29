<?php

namespace App\Http\Controllers;

use App\Ai\Agents\TicketClassifier;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['message' => 'required|string']);

        $classification = (new TicketClassifier)->prompt($request->message);

        $ticket = $request->user()->tickets()->create([
            'message' => $request->message,
            'category' => $classification['category'],
            'priority' => $classification['priority'],
            'sentiment' => $classification['sentiment'],
            'summary' => $classification['summary'],
        ]);

        return response()->json($ticket, 201);
    }
}
