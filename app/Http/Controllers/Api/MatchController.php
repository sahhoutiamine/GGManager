<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateScoreRequest;
use App\Http\Resources\MatchResource;
use App\Models\TournamentMatch;
use App\Events\ScoreUpdated;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MatchController extends Controller
{
    use AuthorizesRequests;

    /**
     * PATCH /api/matches/{match}/score
     *
     * Update the score of a match and propagate the winner to the next round.
     * Only the organizer who owns the tournament may perform this action.
     */
    public function updateScore(UpdateScoreRequest $request, TournamentMatch $match): MatchResource
    {
        $this->authorize('updateScore', $match);

        $validated = $request->validated();

        // Persist result on current match
        $match->update([
            'score'     => $validated['score'],
            'winner_id' => $validated['winner_id'],
            'status'    => 'finished',
        ]);

        // ── Winner propagation ───────────────────────────────────────────────
        // Move the winner into the next match as player1 or player2 depending
        // on which slot is still empty.
        if ($match->next_match_id) {
            $nextMatch = TournamentMatch::find($match->next_match_id);

            if ($nextMatch) {
                if (is_null($nextMatch->player1_id)) {
                    $nextMatch->update(['player1_id' => $validated['winner_id']]);
                } elseif (is_null($nextMatch->player2_id)) {
                    $nextMatch->update(['player2_id' => $validated['winner_id']]);
                }
            }
        }

        // ── Real-time broadcast ──────────────────────────────────────────────
        broadcast(new ScoreUpdated($match->fresh(['player1', 'player2', 'winner'])))->toOthers();

        return new MatchResource($match->fresh(['player1', 'player2', 'winner']));
    }

    // Liste des matchs d'un tournoi spécifique
    public function index($tournamentId)
    {
        $matches = TournamentMatch::where('tournament_id', $tournamentId)
            ->with(['player1', 'player2', 'winner'])
            ->orderBy('round')
            ->orderBy('position')
            ->get();

        return MatchResource::collection($matches);
    }

    // Voir un match spécifique
    public function show(TournamentMatch $match)
    {
        return new MatchResource($match->load(['player1', 'player2', 'winner']));
    }

    // Reset le score d'un match (DELETE technique du score)
    public function resetScore(TournamentMatch $match)
    {
        $this->authorize('updateScore', $match);

        $match->update([
            'score' => null,
            'winner_id' => null,
            'status' => 'scheduled',
        ]);

        return response()->json(['message' => 'Score reset successfully']);
    }
}