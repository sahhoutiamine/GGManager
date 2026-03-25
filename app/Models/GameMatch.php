<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'tournament_id', 'bracket_id', 'next_match_id', 
        'round', 'position', 'player1_id', 'player2_id', 
        'winner_id', 'score', 'status'
    ];

    public function player1(): BelongsTo { return $this->belongsTo(User::class, 'player1_id'); }
    public function player2(): BelongsTo { return $this->belongsTo(User::class, 'player2_id'); }
    public function winner(): BelongsTo { return $this->belongsTo(User::class, 'winner_id'); }

    public function nextMatch(): BelongsTo
    {
        return $this->belongsTo(GameMatch::class, 'next_match_id');
    }
}
