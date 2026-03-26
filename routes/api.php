<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TournamentRegistrationController;

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// player routes
Route::get('tournaments', [TournamentController::class, 'index']);
Route::get('tournaments/{tournament}', [TournamentController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Tournament organizer routes
    Route::post('tournaments', [TournamentController::class, 'store']);
    Route::put('tournaments/{tournament}', [TournamentController::class, 'update']);
    Route::delete('tournaments/{tournament}', [TournamentController::class, 'destroy']);
    
    // Organizer retrieving participants
    Route::get('tournaments/{tournament}/participants', [TournamentRegistrationController::class, 'participants']);

    // Player registering for tournament
    Route::post('tournaments/{tournament}/register', [TournamentRegistrationController::class, 'register']);
});
