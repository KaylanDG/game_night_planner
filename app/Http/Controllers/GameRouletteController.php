<?php

namespace App\Http\Controllers;

use App\Models\GameSession;
use App\Services\GameRoulette;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class GameRouletteController extends Controller
{
    /**
     * Pick the winning suggestion; the wheel on the session page animates toward it.
     * Nothing is stored, so the creator can spin as often as they like.
     */
    public function __invoke(GameSession $session, GameRoulette $roulette): JsonResponse
    {
        Gate::authorize('spin', $session);

        $winner = $roulette->spin($session);

        if ($winner === null) {
            return response()->json(['message' => 'There are no suggested games to spin.'], 422);
        }

        return response()->json([
            'suggestion_id' => $winner->id,
            'game_id' => $winner->game->id,
            'igdb_id' => $winner->game->igdb_id,
            'name' => $winner->game->name,
            'cover_url' => $winner->game->cover_url,
        ]);
    }
}
