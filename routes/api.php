<?php

use App\Http\Controllers\TestPlatformController;
use Illuminate\Support\Facades\Route;

Route::prefix('test')->controller(TestPlatformController::class)->group(function (): void {
    Route::post('/games', 'createGame');
    Route::post('/deposit', 'deposit');
    Route::post('/entry', 'placeEntry');
    Route::get('/games', 'listGames');
    Route::post('/result', 'declareResult');
});
