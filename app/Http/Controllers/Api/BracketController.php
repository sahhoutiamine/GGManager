<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BracketResource;
use App\Models\Tournament;
use Illuminate\Http\JsonResponse;

class BracketController extends Controller
{
    /**
     * GET /api/tournaments/{tournament}/bracket
     *
     * Returns the full bracket tree (round-by-round) for a tournament.
     * Public endpoint — no authentication required.
     */
    public function show(Tournament $tournament): BracketResource|JsonResponse
    {
        $bracket = $tournament->bracket;

        if (! $bracket) {
            return response()->json([
                'message' => 'Bracket has not been generated yet for this tournament.',
            ], 404);
        }

        return new BracketResource($bracket);
    }
}
