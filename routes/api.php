<?php

use App\Http\Controllers\Api\BookingApiController;
use Illuminate\Support\Facades\Route;

Route::get('/services', [BookingApiController::class, 'services']);
Route::get('/masters', [BookingApiController::class, 'masters']);
Route::get('/slots', [BookingApiController::class, 'slots']);
Route::post('/appointments', [BookingApiController::class, 'store'])->middleware('throttle:10,1');
