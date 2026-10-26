<?php

namespace App\Http\Controllers;

use App\Exceptions\IgdbException;
use App\Models\Game;
use App\Models\GameSession;
use App\Services\IgdbService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GameController extends Controller
{
    public function __construct(private readonly IgdbService $igdb) {}

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = $validated['search'] ?? null;
        $page = (int) ($validated['page'] ?? 1);

        try {
            ['games' => $games, 'has_more' => $hasMore] = $this->igdb->search($search, $page);
        } catch (IgdbException $e) {
            [$games, $hasMore, $error] = [[], false, $e->getMessage()];
        }

        return view('games.index', [
            'games' => $games,
            'search' => $search,
            'page' => $page,
            'hasMore' => $hasMore,
            'error' => $error ?? null,
        ]);
    }

    public function show(int $igdbId): View
    {
        try {
            $game = $this->igdb->find($igdbId);
        } catch (IgdbException $e) {
            return view('games.show', [
                'game' => null,
                'sessions' => collect(),
                'suggestedSessionIds' => [],
                'error' => $e->getMessage(),
            ]);
        }

        abort_if($game === null, 404);

        return view('games.show', [
            'game' => $game,
            'sessions' => GameSession::upcoming()->withCount('goingAttendees')->get(),
            'suggestedSessionIds' => Game::firstWhere('igdb_id', $igdbId)?->gameSessions()->pluck('game_sessions.id')->all() ?? [],
            'error' => null,
        ]);
    }
}
