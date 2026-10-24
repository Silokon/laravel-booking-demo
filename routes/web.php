<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BookingController::class, 'index'])->name('booking.index');
Route::post('/book', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');
