<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Models\GameSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function update(Request $request, GameSession $session): RedirectResponse
    {
        Gate::authorize('respond', $session);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(AttendanceStatus::class)],
        ]);

        // Inserts or updates the single pivot row for this user and session.
        $session->attendees()->syncWithoutDetaching([
            $request->user()->id => ['status' => $validated['status']],
        ]);

        return redirect()->route('sessions.show', $session);
    }
}
