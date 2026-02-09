<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/weather', [WeatherController::class, 'index']);
    Route::get('/weather/search', [WeatherController::class, 'search']);

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites', [FavoriteController::class, 'store']);
    Route::delete('/favorites/{id}', [FavoriteController::class, 'destroy']);
});
