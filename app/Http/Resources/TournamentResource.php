<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TournamentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'game' => $this->game,
            'start_date' => $this->start_date?->toDateTimeString(),
            'max_participants' => $this->max_participants,
            'format' => $this->format,
            'status' => $this->status,
            'organizer' => [
                'id' => $this->organizer_id,
                'name' => $this->organizer->name,
            ],
            'registrations_count' => $this->registrations_count ?? $this->registrations()->count(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
