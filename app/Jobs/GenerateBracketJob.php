<?php

namespace App\Jobs;

use App\Models\Tournament;
use App\Services\BracketService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateBracketJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = 5;

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
