<?php

namespace App\Http\Controllers\Api;

use App\Jobs\GenerateBracketJob;
use App\Http\Controllers\Controller;
use App\Http\Requests\TournamentRequest;
use App\Http\Resources\TournamentResource;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TournamentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of tournaments with filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Tournament::with('organizer')->withCount('registrations');

        // Filter by game
        if ($request->has('game')) {
            $query->where('game', $request->game);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return TournamentResource::collection($query->get());
    }

    /**
     * Store a newly created tournament.
     */
    public function store(TournamentRequest $request): TournamentResource
    {
        $this->authorize('create', Tournament::class);

        $tournament = Tournament::create([
            ...$request->validated(),
            'organizer_id' => $request->user()->id,
            'format' => 'single elimination',
            'status' => 'open',
        ]);

        return new TournamentResource($tournament);
    }

    /**
     * Display the specified tournament.
     */
    public function show(Tournament $tournament): TournamentResource
    {
        return new TournamentResource($tournament->load(['organizer', 'registrations']));
    }

    /**
     * Update the specified tournament.
     */
    public function update(TournamentRequest $request, Tournament $tournament): TournamentResource
    {
        $this->authorize('update', $tournament);

        $tournament->update($request->validated());

        return new TournamentResource($tournament);
    }

    /**
     * Remove the specified tournament.
     */
    public function destroy(Tournament $tournament)
    {
        $this->authorize('delete', $tournament);

        $tournament->delete();

        return response()->noContent();
    }
    public function closeRegistration(Tournament $tournament){
        $tournament->update([
            'status'=>'closed'
        ]);
        GenerateBracketJob::dispatch($tournament);

        return $response->json([
        'message'=>'Bracket generation started'
        ]);
    }
}
