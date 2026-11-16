<?php

use App\Http\Controllers\Api\AirQualityController;
use App\Http\Controllers\Api\CityController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'time' => now()->toIso8601String(),
]));

Route::get('/cities', [CityController::class, 'index']);
Route::get('/air-quality/ranking', [AirQualityController::class, 'ranking']);
Route::get('/air-quality/cities/{city:slug}/series', [AirQualityController::class, 'series']);
