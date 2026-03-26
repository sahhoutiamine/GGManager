<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BracketResource extends JsonResource
{
    /**
     * Transform the bracket into a hierarchical round-by-round structure.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Group matches by round number
        $rounds = $this->resource
            ->matches()
            ->with(['player1', 'player2', 'winner'])
            ->orderBy('round')
            ->orderBy('position')
            ->get()
            ->groupBy('round')
            ->map(fn ($matches, $round) => [
                'round'   => (int) $round,
                'matches' => MatchResource::collection($matches),
            ])
            ->values();

        return [
            'id'           => $this->id,
            'tournament_id'=> $this->tournament_id,
            'total_rounds' => $this->total_rounds,
            'rounds'       => $rounds,
        ];
    }
}
