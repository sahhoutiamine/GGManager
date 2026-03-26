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

        $query->game($request->query('game'))
              ->status($request->query('status'))
              ->latest();

        return TournamentResource::collection($query->paginate(10));
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
    /**
     * Close registration and trigger async bracket generation.
     * Only the tournament's organizer may do this.
     */
    public function closeRegistration(Tournament $tournament): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $tournament);

        if ($tournament->status !== 'open') {
            return response()->json([
                'message' => 'Tournament registration is already closed.',
            ], 422);
        }

        if ($tournament->bracket()->exists()) {
            return response()->json([
                'message' => 'Bracket has already been generated for this tournament.',
            ], 422);
        }

        $tournament->update(['status' => 'closed']);

        GenerateBracketJob::dispatch($tournament);

        return response()->json([
            'message' => 'Registration closed. Bracket generation has been queued.',
        ], 202);
    }
}
