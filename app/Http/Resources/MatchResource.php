<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tournament_id'=> $this->tournament_id,
            'bracket_id'   => $this->bracket_id,
            'round'        => $this->round,
            'position'     => $this->position,
            'status'       => $this->status,
            'score'        => $this->score,
            'next_match_id'=> $this->next_match_id,
            'player1'      => $this->whenLoaded('player1', fn () => [
                'id'   => $this->player1->id,
                'name' => $this->player1->name,
            ]),
            'player2'      => $this->whenLoaded('player2', fn () => [
                'id'   => $this->player2->id,
                'name' => $this->player2->name,
            ]),
            'winner'       => $this->whenLoaded('winner', fn () => $this->winner ? [
                'id'   => $this->winner->id,
                'name' => $this->winner->name,
            ] : null),
        ];
    }
}
