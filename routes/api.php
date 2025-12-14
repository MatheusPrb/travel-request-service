<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TravelOrderController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/travel-orders', [TravelOrderController::class, 'store']);
    Route::get('/travel-orders', [TravelOrderController::class, 'index']);
    Route::get('/travel-orders/{id}', [TravelOrderController::class, 'show']);

    Route::middleware('admin')->group(function () {
        Route::post('/users/promote-to-admin', [AuthController::class, 'promoteToAdmin']);
        Route::patch('/travel-orders/{id}/status', [TravelOrderController::class, 'updateStatus']);
    });
});
