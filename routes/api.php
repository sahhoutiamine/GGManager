<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\TournamentController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// player routes
Route::get('tournaments', [TournamentController::class, 'index']);
Route::get('tournaments/{tournament}', [TournamentController::class, 'show']);

// organizer routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('tournaments', [TournamentController::class, 'store']);
    Route::put('tournaments/{tournament}', [TournamentController::class, 'update']);
    Route::delete('tournaments/{tournament}', [TournamentController::class, 'destroy']);
    Route::patch('matches/{match}/score', [MatchController::class, 'updateScore']);
});
