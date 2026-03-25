<?php

namespace App\Policies;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TournamentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any tournaments.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the tournament.
     */
    public function view(?User $user, Tournament $tournament): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create tournaments.
     */
    public function create(User $user): bool
    {
        return $user->role === 'organizer';
    }

    /**
     * Determine whether the user can update the tournament.
     */
    public function update(User $user, Tournament $tournament): bool
    {
        return $user->id === $tournament->organizer_id 
               && !$tournament->hasStartedMatches();
    }

    /**
     * Determine whether the user can delete the tournament.
     */
    public function delete(User $user, Tournament $tournament): bool
    {
        return $user->id === $tournament->organizer_id 
               && !$tournament->hasStartedMatches();
    }
}
