<?php

use App\Ai\Agents\SupportAgent;
use App\Ai\Agents\TicketClassifier;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\TicketController;
use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/support/test', function () {
    $response = (new SupportAgent)->prompt(
        'Hi, I placed an order three days ago and it still says processing. Order number is #1042.'
    );

    return [
        'reply' => $response->text,
        'prompt_tokens' => $response->usage->promptTokens,
        'completion_tokens' => $response->usage->completionTokens,
        'provider' => $response->meta->provider,
        'model' => $response->meta->model,
    ];
});

Route::get('/classify/test', function () {
    $result = (new TicketClassifier)->prompt('Hey, just wondering when my package will arrive? Order #1055. No rush, just curious!');

    return [
        'category' => $result['category'],
        'priority' => $result['priority'],
        'sentiment' => $result['sentiment'],
        'summary' => $result['summary'],
    ];
});

Route::post('/tickets', [TicketController::class, 'store'])->middleware('auth');

Route::get('/memory/problem', function () {
    $agent = new SupportAgent;

    $first = $agent->prompt('Hi, my order #1042 seems to be lost.');

    $second = $agent->prompt('Can you just resend it?');

    return [
        'first' => $first->text,
        'second' => $second->text,
    ];
});

Route::get('/chat/start', function () {
    $user = User::first();
    $agent = new SupportAgent;

    $response = $agent->forUser($user)->prompt(
        'Hi, my order #1042 seems to be lost. It was supposed to arrive last week.'
    );

    return [
        'reply' => $response->text,
        'conversation_id' => $response->conversationId,
    ];
});

Route::get('/chat/continue/{conversationId}', function (string $conversationId) {
    $user = User::first();
    $agent = new SupportAgent;

    $response = $agent->continue($conversationId, as: $user)->prompt(
        'Can you just send a replacement instead?'
    );

    return $response->text;
});

Route::get('/chat/resume', function () {
    $user = User::first();
    $agent = new SupportAgent;

    $conversationId = session('last_conversation_id');

    $response = $agent->continue($conversationId, as: $user)->prompt(
        'Actually, can you check my email for the shipping confirmation too?'
    );

    return $response->text;
});

Route::get('/kb/search', function () {
    $query = request('q', 'How do I return a damaged item?');

    $results = KnowledgeArticle::query()
        ->whereVectorSimilarTo('embedding', $query)
        ->limit(3)
        ->get();

    return $results->map(fn ($article) => [
        'title' => $article->title,
        'category' => $article->category,
        'excerpt' => str($article->content)->limit(100),
    ]);
});

Route::get('/support/kb-test', function () {
    $response = (new SupportAgent)->prompt('What is your return policy for damaged items?');

    return $response->text;
});

Route::get('/support/rag-test', function () {
    $agent = new SupportAgent;
    $user = User::first();

    $response = $agent->forUser($user)->prompt('My order #1042 arrived damaged. What should I do?
    ');

    return $response->text;
});

Route::post('/chat', [ChatController::class, 'send'])->middleware('auth');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
