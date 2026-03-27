<?php

namespace App\Services;

use App\Models\TournamentMatch;
use App\Events\ScoreUpdated;
use Illuminate\Support\Facades\DB;

class MatchService
{
    /**
     * Update the score of a match and propagate the winner to the next round.
     */
    public function updateScore(TournamentMatch $match, string $score, int $winnerId): TournamentMatch
    {
        return DB::transaction(function () use ($match, $score, $winnerId) {
            $match->update([
                'score'     => $score,
                'winner_id' => $winnerId,
                'status'    => 'finished',
            ]);

            // Propagate winner to next match if applicable
            if ($match->next_match_id) {
                $nextMatch = TournamentMatch::lockForUpdate()->find($match->next_match_id);

                if ($nextMatch) {
                    // To ensure idempotency and correct slot assignment, we shouldn't just grab an empty slot.
                    // Usually, if a match's position is odd/even, it indicates whether it's player 1 or 2 for the next match.
                    // Let's use the current match's position to determine the slot.
                    // If position is odd (1, 3, 5...), it feeds into player1_id. Even (2, 4...) feeds into player2_id.
                    if ($match->position % 2 !== 0) {
                        $nextMatch->update(['player1_id' => $winnerId]);
                    } else {
                        $nextMatch->update(['player2_id' => $winnerId]);
                    }
                }
            }

            $match->refresh()->load(['player1', 'player2', 'winner']);

            // Broadcast real-time update
            broadcast(new ScoreUpdated($match))->toOthers();

            return $match;
        });
    }

    /**
     * Reset the score of a match and reverse winner propagation.
     */
    public function resetScore(TournamentMatch $match): TournamentMatch
    {
        return DB::transaction(function () use ($match) {
            $oldWinnerId = $match->winner_id;

            $match->update([
                'score'     => null,
                'winner_id' => null,
                'status'    => 'scheduled',
            ]);

            // Reverse propagation
            if ($match->next_match_id && $oldWinnerId) {
                $nextMatch = TournamentMatch::lockForUpdate()->find($match->next_match_id);

                if ($nextMatch) {
                    if ($match->position % 2 !== 0) {
                        // If player 1 was the propagated winner, clear it
                        if ($nextMatch->player1_id === $oldWinnerId) {
                            $nextMatch->update(['player1_id' => null]);
                        }
                    } else {
                        // If player 2 was the propagated winner, clear it
                        if ($nextMatch->player2_id === $oldWinnerId) {
                            $nextMatch->update(['player2_id' => null]);
                        }
                    }
                }
            }

            return $match->refresh()->load(['player1', 'player2', 'winner']);
        });
    }
}
