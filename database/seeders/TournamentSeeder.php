<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TournamentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the organizer
        $organizer_id = DB::table('users')->where('role', 'organizer')->first()->id;

        // 1. Create a tournament
        $tournament_id = DB::table('tournaments')->insertGetId([
            'organizer_id' => $organizer_id,
            'name' => 'World Hearthstone Championship',
            'game' => 'Hearthstone',
            'start_date' => Carbon::now()->addDays(7),
            'max_participants' => 8,
            'format' => 'single elimination',
            'status' => 'open',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 2. Create some registrations (8 players)
        $player_ids = DB::table('users')->where('role', 'player')->limit(8)->pluck('id');
        
        foreach ($player_ids as $player_id) {
            DB::table('registrations')->insert([
                'user_id' => $player_id,
                'tournament_id' => $tournament_id,
                'registered_at' => Carbon::now(),
                'status' => 'confirmed',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // 3. Create a bracket
        $bracket_id = DB::table('brackets')->insertGetId([
            'tournament_id' => $tournament_id,
            'total_rounds' => 3, // For 8 participants (log2(8))
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // 4. Create first round matches (4 matches for 8 participants)
        for ($i = 0; $i < 4; $i++) {
            DB::table('matches')->insert([
                'tournament_id' => $tournament_id,
                'bracket_id' => $bracket_id,
                'round' => 1,
                'position' => $i + 1,
                'player1_id' => $player_ids[$i],
                'player2_id' => $player_ids[$i+4],
                'status' => 'scheduled',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
