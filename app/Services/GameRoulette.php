<?php

namespace App\Services;

use App\Models\GameSession;
use App\Models\Suggestion;
use Random\Randomizer;

class GameRoulette
{
    public function __construct(private readonly Randomizer $randomizer = new Randomizer) {}

    /**
     * Pick one of the session's suggestions with equal probability, ignoring votes.
     * Returns null when nothing has been suggested.
     */
    public function spin(GameSession $session): ?Suggestion
    {
        $suggestions = $session->suggestions()->with('game')->orderBy('id')->get();

        if ($suggestions->isEmpty()) {
            return null;
        }

        return $suggestions[$this->randomizer->getInt(0, $suggestions->count() - 1)];
    }
}
