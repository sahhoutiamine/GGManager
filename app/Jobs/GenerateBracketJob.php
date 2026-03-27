<?php

namespace App\Jobs;

use App\Models\Tournament;
use App\Services\BracketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateBracketJob implements ShouldQueue
{
    use Dispatchable, Queueable;
    protected $tournament;

    /**
     * Create a new job instance.
     */
    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
    }

    /**
     * Execute the job.
     */
    public function handle(BracketService $bracketService)
    {
        $bracketService->generate($this->tournament);
    }
}
