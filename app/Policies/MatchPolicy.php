<?php

namespace App\Policies;

use App\Models\TournamentMatch;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MatchPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the match.
     */
    public function view(?User $user, TournamentMatch $match): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the score of the match.
     */
    public function updateScore(User $user, TournamentMatch $match): bool
    {
        // Only the organizer who owns the tournament of the match can update its score
        return $user->role === 'organizer' && $user->id === $match->tournament->organizer_id;
    }
}
