<?php

namespace App\Http\Controllers;

use App\Http\Requests\GameSessionRequest;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class GameSessionController extends Controller
{
    public function index(): View
    {
        return view('sessions.index', [
            'upcoming' => GameSession::upcoming()->with(['creator', 'games'])->withCount('goingAttendees')->get(),
            'awaitingCompletion' => GameSession::awaitingCompletion()->with('creator')->withCount(['goingAttendees', 'games'])->get(),
            'completed' => GameSession::completed()->with(['goingAttendees', 'playedGames'])->withCount('games')->get(),
        ]);
    }

    public function create(): View
    {
        return view('sessions.create');
    }

    public function store(GameSessionRequest $request): RedirectResponse
    {
        $session = $request->user()->createdSessions()->create($request->sessionAttributes());

        return redirect()->route('sessions.show', $session);
    }

    public function show(GameSession $session): View
    {
        $session->load(['creator', 'attendees', 'games', 'suggestions' => fn ($query) => $query->with(['game', 'suggester', 'votes'])]);

        return view('sessions.show', [
            'session' => $session,
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function edit(GameSession $session): View
    {
        Gate::authorize('update', $session);

        return view('sessions.edit', ['session' => $session]);
    }

    public function update(GameSessionRequest $request, GameSession $session): RedirectResponse
    {
        Gate::authorize('update', $session);

        $session->update($request->sessionAttributes());

        return redirect()->route('sessions.show', $session);
    }

    public function destroy(GameSession $session): RedirectResponse
    {
        Gate::authorize('delete', $session);

        $session->delete();

        return redirect()->route('sessions.index');
    }
}
