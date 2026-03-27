<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BracketController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\TournamentRegistrationController;

// ─── Auth ────────────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ─── Public Tournament Routes ────────────────────────────────────────────────
Route::get('tournaments',                            [TournamentController::class, 'index']);
Route::get('tournaments/{tournament}',               [TournamentController::class, 'show']);
Route::get('tournaments/{tournament}/bracket',       [BracketController::class,    'show']);

// ─── Authenticated Routes ─────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Current user
    Route::get('/user', fn (Request $request) => $request->user());

    // ─── Organizer Routes ────────────────────────────────────────────────────────
    Route::middleware('role.organizer')->group(function () {
        Route::post('tournaments',                              [TournamentController::class, 'store']);
        Route::put('tournaments/{tournament}',                  [TournamentController::class, 'update']);
        Route::delete('tournaments/{tournament}',               [TournamentController::class, 'destroy']);
        Route::post('tournaments/{tournament}/close-registration', [TournamentController::class, 'closeRegistration']);
        
        Route::get('tournaments/{tournament}/participants',     [TournamentRegistrationController::class, 'participants']);
        
        Route::patch('matches/{match}/score',                   [MatchController::class, 'updateScore']);
        Route::delete('matches/{match}/score',                  [MatchController::class, 'resetScore']);
    });

    // ─── Player Routes ───────────────────────────────────────────────────────────
    Route::middleware('role.player')->group(function () {
        Route::post('tournaments/{tournament}/register',        [TournamentRegistrationController::class, 'register']);
    });

    // ─── Authenticated Public-ish Routes ─────────────────────────────────────────
    Route::get('tournaments/{tournament}/matches', [MatchController::class, 'index']);
    Route::get('matches/{match}', [MatchController::class, 'show']);
});