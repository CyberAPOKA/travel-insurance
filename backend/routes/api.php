<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuoteController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware([HandlePrecognitiveRequests::class]);
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware([HandlePrecognitiveRequests::class]);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/quotes', [QuoteController::class, 'index']);
    Route::get('/quotes/{quote}', [QuoteController::class, 'show']);
    Route::post('/quotes', [QuoteController::class, 'store'])
        ->middleware([HandlePrecognitiveRequests::class]);
    Route::put('/quotes/{quote}', [QuoteController::class, 'update'])
        ->middleware([HandlePrecognitiveRequests::class]);
});
