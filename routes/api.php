<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BracketController;
use App\Http\Controllers\Api\MatchController;
use App\Http\Controllers\Api\TournamentController;
<<<<<<< HEAD

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

<<<<<<< HEAD
<<<<<<< HEAD
=======
=======
>>>>>>> 0c306791ddbabb85a01706b6909a8b9d9fc82a41
use App\Http\Controllers\Api\TournamentController;
use App\Http\Controllers\Api\AuthController;
=======
>>>>>>> 01e65a4b34ba2366ff75241a187f0bf612ca8b03
use App\Http\Controllers\Api\TournamentRegistrationController;

// ─── Auth ────────────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
<<<<<<< HEAD
Route::post('/login', [AuthController::class, 'login']);
<<<<<<< HEAD
>>>>>>> bd07f113d78c7a4ab10fa5dbd01e704e1954d758
=======
>>>>>>> 0c306791ddbabb85a01706b6909a8b9d9fc82a41
=======
Route::post('/login',    [AuthController::class, 'login']);
>>>>>>> 01e65a4b34ba2366ff75241a187f0bf612ca8b03

// ─── Public Tournament Routes ────────────────────────────────────────────────
Route::get('tournaments',                            [TournamentController::class, 'index']);
Route::get('tournaments/{tournament}',               [TournamentController::class, 'show']);
Route::get('tournaments/{tournament}/bracket',       [BracketController::class,    'show']);

// ─── Authenticated Routes ─────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

<<<<<<< HEAD
    // Tournament organizer routes
    Route::post('tournaments', [TournamentController::class, 'store']);
    Route::put('tournaments/{tournament}', [TournamentController::class, 'update']);
    Route::delete('tournaments/{tournament}', [TournamentController::class, 'destroy']);
<<<<<<< HEAD
<<<<<<< HEAD
    Route::patch('matches/{match}/score', [MatchController::class, 'updateScore']);
=======
=======
    Route::patch('matches/{match}/score', [MatchController::class, 'updateScore']);
>>>>>>> 0c306791ddbabb85a01706b6909a8b9d9fc82a41
    
    // Organizer retrieving participants
    Route::get('tournaments/{tournament}/participants', [TournamentRegistrationController::class, 'participants']);

    // Player registering for tournament
    Route::post('tournaments/{tournament}/register', [TournamentRegistrationController::class, 'register']);
<<<<<<< HEAD
>>>>>>> bd07f113d78c7a4ab10fa5dbd01e704e1954d758
=======
>>>>>>> 0c306791ddbabb85a01706b6909a8b9d9fc82a41
=======
    // Current user
    Route::get('/user', fn (Request $request) => $request->user());

    // Tournament management (organizer)
    Route::post('tournaments',                              [TournamentController::class, 'store']);
    Route::put('tournaments/{tournament}',                  [TournamentController::class, 'update']);
    Route::delete('tournaments/{tournament}',               [TournamentController::class, 'destroy']);
    Route::post('tournaments/{tournament}/close-registration', [TournamentController::class, 'closeRegistration']);

    // Participants (organizer only)
    Route::get('tournaments/{tournament}/participants',     [TournamentRegistrationController::class, 'participants']);

    // Player registration
    Route::post('tournaments/{tournament}/register',        [TournamentRegistrationController::class, 'register']);

    // Match score (organizer only)
    Route::patch('matches/{match}/score',                  [MatchController::class, 'updateScore']);
>>>>>>> 01e65a4b34ba2366ff75241a187f0bf612ca8b03
});
