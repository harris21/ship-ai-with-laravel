<?php

use App\Ai\Agents\SupportAgent;
use Illuminate\Support\Facades\Route;
use Laravel\Ai\Enums\Lab;

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

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
