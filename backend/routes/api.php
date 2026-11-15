<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AirQualityController;
use App\Http\Controllers\Api\CityController;

Route::get('/health', fn () => response()->json(['status' => 'ok']));
Route::get('/cities', [CityController::class, 'index']);
Route::get('/air-quality/ranking', [AirQualityController::class, 'ranking']);
Route::get('/air-quality/cities/{city}/series', [AirQualityController::class, 'series']);
