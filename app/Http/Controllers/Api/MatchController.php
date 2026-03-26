<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TournamentMatch;
use App\Events\ScoreUpdated;
use Illuminate\Http\Request;

class MatchController extends Controller
{
    public function updateScore(Request $request, TournamentMatch $match)
    {
        // Validation : score requis et winner_id doit être soit le player1 soit le player2
        $validated = $request->validate([
            'score' => 'required|string',
            'winner_id' => 'required|exists:users,id|in:' . $match->player1_id . ',' . $match->player2_id,
        ]);

        // Mise à jour du match
        $match->update([
            'score' => $validated['score'],
            'winner_id' => $validated['winner_id'],
            'status' => 'finished',
        ]);

        // Déclenchement du broadcast pour les spectateurs
        broadcast(new ScoreUpdated($match))->toOthers();

        return response()->json([
            'message' => 'Score updated and broadcasted successfully',
            'match' => $match
        ]);
    }
}