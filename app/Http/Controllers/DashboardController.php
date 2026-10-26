<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'totalSessions' => GameSession::completed()->count(),
            'gamesPlayed' => Game::has('playedSessions')->count(),
            'mostPlayedGames' => Game::has('playedSessions')
                ->withCount('playedSessions')
                ->orderByDesc('played_sessions_count')
                ->orderBy('name')
                ->limit(3)
                ->get(),
            'nextSession' => GameSession::upcoming()
                ->with(['creator', 'attendees'])
                ->withCount(['goingAttendees', 'games'])
                ->first(),
            'members' => User::orderBy('name')->get(),
        ]);
    }
}
