<?php

use App\Http\Controllers\Api\CryptoDepositController;
use App\Http\Controllers\Api\CryptoWebhookController;
use App\Http\Controllers\TestPlatformController;
use Illuminate\Support\Facades\Route;

Route::prefix('test')->controller(TestPlatformController::class)->group(function (): void {
    Route::post('/games', 'createGame');
    Route::post('/deposit', 'deposit');
    Route::post('/entry', 'placeEntry');
    Route::get('/games', 'listGames');
    Route::post('/result', 'declareResult');
});

Route::prefix('api')->group(function (): void {
    Route::post('/deposit', [CryptoDepositController::class, 'store'])
        ->middleware('throttle:crypto-deposit-create')
        ->name('crypto.deposits.store');

    Route::get('/deposit/{paymentId}/status', [CryptoDepositController::class, 'show'])
        ->name('crypto.deposits.status');

    Route::post('/webhook/crypto', CryptoWebhookController::class)
        ->name('crypto.webhook.nowpayments');
});
