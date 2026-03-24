<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->foreignId('bracket_id')->constrained()->onDelete('cascade');
            $table->foreignId('next_match_id')->nullable()->constrained('matches')->onDelete('cascade');
            $table->integer('round');
            $table->integer('position');
            
            // Participants (nullable for future rounds)
            $table->foreignId('player1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('player2_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Match result
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('score')->nullable();
            
            $table->enum('status', ['scheduled', 'in_progress', 'finished'])->default('scheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
