<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TravelOrderController;

Route::post('/travel-orders', [TravelOrderController::class, 'store']);
Route::get('/travel-orders', [TravelOrderController::class, 'index']);
Route::get('/travel-orders/{id}', [TravelOrderController::class, 'show']);
