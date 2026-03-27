<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateScoreRequest;
use App\Http\Resources\MatchResource;
use App\Models\TournamentMatch;
use App\Services\MatchService;
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
    public function updateScore(UpdateScoreRequest $request, TournamentMatch $match, MatchService $matchService): MatchResource
    {
        $this->authorize('updateScore', $match);

        $validated = $request->validated();

        $updatedMatch = $matchService->updateScore(
            $match,
            $validated['score'],
            $validated['winner_id']
        );

        return new MatchResource($updatedMatch);
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

    public function resetScore(TournamentMatch $match, MatchService $matchService)
    {
        $this->authorize('updateScore', $match);

        $matchService->resetScore($match);

        return response()->json(['message' => 'Score reset successfully']);
    }
}