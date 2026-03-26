<?php

namespace App\Events;

use App\Models\TournamentMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScoreUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $match;

    public function __construct(TournamentMatch $match)
    {
        $this->match = $match;
    }

    public function broadcastOn(): Channel
    {
        // Canal public basé sur l'ID du tournoi
        return new Channel('tournament.' . $this->match->tournament_id);
    }

    public function broadcastAs(): string
    {
        return 'score.updated';
    }
}