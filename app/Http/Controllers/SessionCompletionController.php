<?php

namespace App\Http\Controllers;

use App\Enums\SessionStatus;
use App\Models\GameSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SessionCompletionController extends Controller
{
    public function create(GameSession $session): View
    {
        Gate::authorize('complete', $session);

        $session->load('games');

        return view('sessions.complete', ['session' => $session]);
    }

    public function store(Request $request, GameSession $session): RedirectResponse
    {
        Gate::authorize('complete', $session);

        $validated = $request->validate([
            'played_games' => ['nullable', 'array'],
            'played_games.*' => [
                'integer',
                'distinct',
                Rule::exists('game_session', 'game_id')->where('game_session_id', $session->id),
            ],
        ]);

        $playedGameIds = $validated['played_games'] ?? [];

        DB::transaction(function () use ($session, $playedGameIds) {
            // Conditional update so a concurrent request can't complete the session twice.
            $completed = GameSession::whereKey($session->id)
                ->where('status', SessionStatus::Planned)
                ->update(['status' => SessionStatus::Completed]);

            abort_if($completed === 0, 409, 'This session has already been completed.');

            $session->suggestions()->whereIn('game_id', $playedGameIds)->update(['was_played' => true]);
        });

        return redirect()->route('sessions.show', $session)->with('status', 'session-completed');
    }
}
