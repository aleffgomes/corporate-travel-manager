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

        Route::prefix('travel-requests')->group(function () {
            Route::get('/', [App\Http\Controllers\Api\TravelRequestController::class, 'index']);
            Route::post('/', [App\Http\Controllers\Api\TravelRequestController::class, 'store']);
            Route::get('/{id}', [App\Http\Controllers\Api\TravelRequestController::class, 'show']);
            Route::put('/{id}', [App\Http\Controllers\Api\TravelRequestController::class, 'update']);
            Route::delete('/{id}', [App\Http\Controllers\Api\TravelRequestController::class, 'destroy']);
            Route::patch('/{id}/status', [App\Http\Controllers\Api\TravelRequestController::class, 'updateStatus']);
            Route::post('/{id}/cancel', [App\Http\Controllers\Api\TravelRequestController::class, 'cancel']);
        });
    });

    Route::get('/ping', function (Request $request) {
        return response()->json([
            'message' => 'pong',
            'timestamp' => now()->toIso8601String()
        ]);
    });
});
