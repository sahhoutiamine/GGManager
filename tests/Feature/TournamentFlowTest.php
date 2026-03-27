<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tournament;
use App\Models\TournamentMatch;

class TournamentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_tournament_lifecycle()
    {
        // 1. Organizer creates tournament
        $organizer = User::create([
            'name' => 'Org',
            'email' => 'org@test.com',
            'password' => bcrypt('password'),
            'role' => 'organizer'
        ]);
        
        $this->actingAs($organizer)->postJson('/api/tournaments', [
            'name' => 'Grand Prix',
            'game' => 'CS:GO',
            'start_date' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'max_participants' => 16,
        ])->assertStatus(201);
        
        $tournament = Tournament::first();

        // 2. Players register
        $player1 = User::create([
            'name' => 'P1',
            'email' => 'p1@test.com',
            'password' => bcrypt('password'),
            'role' => 'player'
        ]);
        $player2 = User::create([
            'name' => 'P2',
            'email' => 'p2@test.com',
            'password' => bcrypt('password'),
            'role' => 'player'
        ]);
        
        $this->actingAs($player1)->postJson("/api/tournaments/{$tournament->id}/register")
             ->assertStatus(201);
        $this->actingAs($player2)->postJson("/api/tournaments/{$tournament->id}/register")
             ->assertStatus(201);

        // 3. Organizer closes registration and generates bracket
        $this->actingAs($organizer)->postJson("/api/tournaments/{$tournament->id}/close-registration")
             ->assertStatus(202); // 202 Accepted because bracket generator is dispatched
             
        $this->assertDatabaseHas('tournaments', [
            'id' => $tournament->id,
            'status' => 'closed'
        ]);

        // At this point bracket is queued. In tests with QUEUE_CONNECTION=sync, it's already generated.
        $bracket = $tournament->bracket()->with('matches')->first();
        $this->assertNotNull($bracket);

        // 4. Update match score
        // 2 players = 1 match since log2(2) = 1 round
        $match = $bracket->matches->first();

        $this->actingAs($organizer)->patchJson("/api/matches/{$match->id}/score", [
            'score' => '2-1',
            'winner_id' => $match->player1_id
        ])->assertStatus(200);

        // Verify propagation / state
        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'winner_id' => $match->player1_id,
            'score' => '2-1',
            'status' => 'finished'
        ]);
        
        // Reset score
        $this->actingAs($organizer)->deleteJson("/api/matches/{$match->id}/score")
             ->assertStatus(200);

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'winner_id' => null,
            'score' => null,
            'status' => 'scheduled'
        ]);
    }
}
