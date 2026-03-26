<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TournamentRegistrationController extends Controller
{
    use AuthorizesRequests;

    /**
     * Register a player to a tournament.
     */
    public function register(Request $request, Tournament $tournament): JsonResponse
    {
        // Only users with role "player" can register
        if ($request->user()->role !== 'player') {
            return response()->json(['message' => 'Only players can register for tournaments.'], 403);
        }

        // Tournament must be open
        if ($tournament->status !== 'open') {
            return response()->json(['message' => 'Tournament is not open for registration.'], 400);
        }

        // Tournament must not be full
        if ($tournament->max_participants > 0 && $tournament->registrations()->count() >= $tournament->max_participants) {
            return response()->json(['message' => 'Tournament is already full.'], 400);
        }

        // Player cannot register twice
        if ($tournament->registrations()->where('user_id', $request->user()->id)->exists()) {
            return response()->json(['message' => 'You are already registered for this tournament.'], 400);
        }

        // Store registration in pivot table (via registrations model/relationship)
        $tournament->registrations()->create([
            'user_id' => $request->user()->id,
            'status' => 'confirmed',
            'registered_at' => now(),
        ]);

        return response()->json([
            'message' => 'Successfully registered to the tournament.'
        ], 201);
    }

    /**
     * Organizer - Liste des inscrits
     */
    public function participants(Request $request, Tournament $tournament): JsonResponse
    {
        // Only "organizer" who owns the tournament can access
        $this->authorize('manageParticipants', $tournament);

        $participants = $tournament->participants;

        return response()->json([
            'data' => \App\Http\Resources\ParticipantResource::collection($participants)
        ]);
    }
}
