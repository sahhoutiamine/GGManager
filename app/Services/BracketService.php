<?php

namespace App\Services;

use App\Models\Bracket;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Support\Collection;
use RuntimeException;

class BracketService
{
    /**
     * Generate a single-elimination bracket for the given tournament.
     *
     * @throws RuntimeException if a bracket already exists or there are not enough players.
     */
    public function generate(Tournament $tournament): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($tournament) {
            // ── Idempotency guard ────────────────────────────────────────────────
            if ($tournament->bracket()->exists()) {
                throw new RuntimeException(
                    "A bracket already exists for tournament [{$tournament->id}]."
                );
            }

            // ── Load confirmed participants ──────────────────────────────────────
            // Status must match what TournamentRegistrationController stores ('confirmed')
            $participants = $tournament->participants()
                ->wherePivot('status', 'confirmed')
                ->get()
                ->shuffle();

            $count = $participants->count();

            if ($count < 2) {
                throw new RuntimeException(
                    "Tournament [{$tournament->id}] needs at least 2 confirmed participants to generate a bracket."
                );
            }

            // ── Round calculation (supports non-power-of-2 with byes) ───────────
            $totalRounds = (int) ceil(log($count, 2));
            $bracketSize = (int) pow(2, $totalRounds); // next power of 2 >= $count
            $byeCount    = $bracketSize - $count;       // byes to fill to a power of 2

            // Fill with nulls (byes) so pairing is symmetric
            $slots = $participants->concat(array_fill(0, $byeCount, null))->values();

            // ── Create bracket record ────────────────────────────────────────────
            $bracket = Bracket::create([
                'tournament_id' => $tournament->id,
                'total_rounds'  => $totalRounds,
            ]);

            $this->createRounds($bracket, $slots, $totalRounds);
        });
    }

    /**
     * Create all rounds and link matches to their parent (next match).
     */
    private function createRounds(Bracket $bracket, Collection $slots, int $totalRounds): void
    {
        $previousRoundMatches = [];

        for ($round = 1; $round <= $totalRounds; $round++) {
            $matchCount          = (int) pow(2, $totalRounds - $round);
            $currentRoundMatches = [];

            for ($position = 1; $position <= $matchCount; $position++) {
                if ($round === 1) {
                    // Assign real players (or null for byes) from slots
                    $p1 = optional($slots->shift())?->id;
                    $p2 = optional($slots->shift())?->id;

                    // If one player has a bye, they automatically advance —
                    // mark the match as finished with the real player as winner
                    $winner = null;
                    $status = 'scheduled';
                    if ($p1 !== null && $p2 === null) {
                        $winner = $p1;
                        $status = 'finished';
                    } elseif ($p1 === null && $p2 !== null) {
                        $winner = $p2;
                        $status = 'finished';
                    }

                    $match = TournamentMatch::create([
                        'tournament_id' => $bracket->tournament_id,
                        'bracket_id'    => $bracket->id,
                        'round'         => $round,
                        'position'      => $position,
                        'player1_id'    => $p1,
                        'player2_id'    => $p2,
                        'winner_id'     => $winner,
                        'status'        => $status,
                    ]);
                } else {
                    // Subsequent rounds: empty slots — filled as winners advance
                    $match = TournamentMatch::create([
                        'tournament_id' => $bracket->tournament_id,
                        'bracket_id'    => $bracket->id,
                        'round'         => $round,
                        'position'      => $position,
                        'player1_id'    => null,
                        'player2_id'    => null,
                        'status'        => 'scheduled',
                    ]);
                }

                $currentRoundMatches[] = $match;
            }

            // ── Link previous round matches to their next_match ──────────────
            foreach ($previousRoundMatches as $index => $prevMatch) {
                $nextMatch = $currentRoundMatches[(int) floor($index / 2)] ?? null;
                if ($nextMatch) {
                    $prevMatch->update(['next_match_id' => $nextMatch->id]);

                    // If this was a bye-match, propagate winner immediately
                    if ($prevMatch->winner_id !== null) {
                        if (is_null($nextMatch->player1_id)) {
                            $nextMatch->update(['player1_id' => $prevMatch->winner_id]);
                        } elseif (is_null($nextMatch->player2_id)) {
                            $nextMatch->update(['player2_id' => $prevMatch->winner_id]);
                        }
                    }
                }
            }

            $previousRoundMatches = $currentRoundMatches;
        }
    }
}
