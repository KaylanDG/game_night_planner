<?php

namespace App\Http\Controllers;

use App\Enums\Vote;
use App\Models\Suggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SuggestionVoteController extends Controller
{
    /**
     * Cast, change or (when repeating the same vote) remove the user's vote.
     */
    public function update(Request $request, Suggestion $suggestion): RedirectResponse
    {
        Gate::authorize('vote', $suggestion);

        $vote = Vote::from($request->validate([
            'vote' => ['required', Rule::enum(Vote::class)],
        ])['vote']);

        $existing = $suggestion->votes()->whereBelongsTo($request->user())->first();

        if ($existing?->vote === $vote) {
            $existing->delete();
        } else {
            $suggestion->votes()->updateOrCreate(['user_id' => $request->user()->id], ['vote' => $vote]);
        }

        return redirect()->to(route('sessions.show', $suggestion->game_session_id)."#suggestion-{$suggestion->id}");
    }
}
