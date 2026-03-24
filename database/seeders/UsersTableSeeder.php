<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // One organizer
        User::create([
            'name' => 'John Organizer',
            'email' => 'organizer@ggmanager.com',
            'password' => Hash::make('password'),
            'role' => 'organizer',
        ]);

        // Multiple players
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "Player $i",
                'email' => "player$i@example.com",
                'password' => Hash::make('password'),
                'role' => 'player',
            ]);
        }
    }
}
