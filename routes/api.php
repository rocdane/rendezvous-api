<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ProfileController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'timezone' => config('app.timezone'),
        'environment' => app()->environment(),
        'locale' => app()->getLocale(),
    ]);
});

// Routes d'authentification
Route::prefix('auth')->group(function () {
    Route::get('{provider}/redirect', [AuthController::class, 'redirectToProvider']);
    Route::get('{provider}/callback', [AuthController::class, 'handleCallback']);
    Route::post('reset', [AuthController::class, 'resetPassword']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::prefix('user')->group(function () {
    Route::apiResource('profiles', ProfileController::class);
});

// Routes protégées par Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('bookings', BookingController::class);
    Route::apiResource('calendars', CalendarController::class);
    
    Route::prefix('user')->group(function () {
        Route::get('availability', [CalendarController::class, 'getAvailability']);
        Route::put('availability', [CalendarController::class, 'updateAvailability']);
    });
});

// Routes publiques pour les réservations
Route::prefix('public')->group(function () {
    Route::get('calendar/{username}', [CalendarController::class, 'getPublicCalendar']);
    Route::post('booking/{username}', [BookingController::class, 'createPublicBooking']);
});
