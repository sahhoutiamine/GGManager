<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\Tournament::class, 
            \App\Policies\TournamentPolicy::class
        );
        \Illuminate\Support\Facades\Gate::policy(
            \App\Models\TournamentMatch::class, 
            \App\Policies\MatchPolicy::class
        );
    }
}
