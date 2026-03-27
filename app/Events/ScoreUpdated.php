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
        return new Channel('tournament.' . $this->match->tournament_id);
    }

    public function broadcastWith(): array
    {
        return [
            'match_id'   => $this->match->id,
            'round'      => $this->match->round,
            'player1_id' => $this->match->player1_id,
            'player2_id' => $this->match->player2_id,
            'score'      => $this->match->score,
            'winner_id'  => $this->match->winner_id,
        ];
    }

    public function broadcastAs(): string
    {
        return 'score.updated';
    }
}