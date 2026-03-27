<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Tournament;
use App\Models\User;
use App\Services\BracketService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BracketServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_bracket_generation_logic_with_non_power_of_two()
    {
        $organizer = User::create([
            'name' => 'Org',
            'email' => 'org@test.com',
            'password' => bcrypt('password'),
            'role' => 'organizer'
        ]);
        
        $tournament = Tournament::create([
            'organizer_id' => $organizer->id,
            'name' => 'Test Tournament',
            'game' => 'CS:GO',
            'start_date' => now()->addDays(2),
            'max_participants' => 16,
            'format' => 'single elimination',
            'status' => 'open'
        ]);

        // Create 6 players (not a power of 2, means 2 byes needed for 8-slot bracket)
        $players = [];
        for ($i = 1; $i <= 6; $i++) {
            $players[] = User::create([
                'name' => "Player $i",
                'email' => "player$i@test.com",
                'password' => bcrypt('password'),
                'role' => 'player'
            ]);
        }
        
        foreach ($players as $player) {
            $tournament->registrations()->create([
                'user_id' => $player->id,
                'status' => 'confirmed'
            ]);
        }

        $service = new BracketService();
        $service->generate($tournament);

        $bracket = $tournament->bracket()->with('matches')->first();

        // 6 players -> math needs log2(6)=2.58 -> ceil=3 rounds.
        // 3 rounds means 2^3 = 8 slots. 8 - 6 = 2 byes.
        $this->assertEquals(3, $bracket->total_rounds);
        $this->assertNotNull($bracket);

        $matches = $bracket->matches;
        // Total matches in single elimination bracket with 8 slots is 7
        // Round 1 = 4 matches, Round 2 = 2 matches, Round 3 = 1 match
        $this->assertCount(7, $matches);

        // Check linkage (next_match_id)
        $round1Match = $matches->where('round', 1)->first();
        $this->assertNotNull($round1Match->next_match_id);

        $finalMatch = $matches->where('round', 3)->first();
        $this->assertNull($finalMatch->next_match_id); // The final has no next match
    }
}
