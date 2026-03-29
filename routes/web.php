<?php

use App\Ai\Agents\SupportAgent;
use App\Ai\Agents\TicketClassifier;
use App\Http\Controllers\TicketController;
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

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
