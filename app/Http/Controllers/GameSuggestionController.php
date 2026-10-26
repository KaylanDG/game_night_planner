<?php

namespace App\Http\Controllers;

use App\Exceptions\IgdbException;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\Suggestion;
use App\Services\IgdbService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class GameSuggestionController extends Controller
{
    public function store(Request $request, int $igdbId, IgdbService $igdb): RedirectResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'integer', 'exists:game_sessions,id'],
        ]);

        $session = GameSession::findOrFail($validated['session_id']);

        if (! Gate::allows('suggestGame', $session)) {
            throw ValidationException::withMessages([
                'session_id' => 'Games can only be added to upcoming sessions.',
            ]);
        }

        $game = Game::firstWhere('igdb_id', $igdbId) ?? $this->importGame($igdb, $igdbId);

        if ($session->games()->whereKey($game->id)->exists()) {
            throw ValidationException::withMessages([
                'session_id' => 'This game has already been suggested for that session.',
            ]);
        }

        $session->games()->attach($game->id, ['suggested_by' => $request->user()->id]);

        return redirect()
            ->route('games.show', $igdbId)
            ->with('suggestedTo', $session->only('id', 'title'));
    }

    public function destroy(Suggestion $suggestion): RedirectResponse
    {
        Gate::authorize('delete', $suggestion);

        // Votes are removed with it via the foreign key cascade.
        $suggestion->delete();

        return redirect()->route('sessions.show', $suggestion->game_session_id);
    }

    /**
     * Store a game locally the first time it's added to a session.
     */
    private function importGame(IgdbService $igdb, int $igdbId): Game
    {
        try {
            $data = $igdb->find($igdbId);
        } catch (IgdbException $e) {
            throw ValidationException::withMessages(['session_id' => $e->getMessage()]);
        }

        abort_if($data === null, 404);

        return Game::firstOrCreate(['igdb_id' => $igdbId], [
            'name' => $data['name'],
            'cover_image_id' => $data['cover_image_id'],
            'release_date' => $data['release_date'],
            'summary' => $data['summary'],
        ]);
    }
}
