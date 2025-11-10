<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String()
    ]);
});

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('/auth/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/auth/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::post('/auth/refresh', [App\Http\Controllers\Api\AuthController::class, 'refresh']);
        Route::get('/auth/me', [App\Http\Controllers\Api\AuthController::class, 'me']);
    });
    Route::get('/ping', function (Request $request) {
        return response()->json([
            'message' => 'pong',
            'timestamp' => now()->toIso8601String()
        ]);
    });
});
