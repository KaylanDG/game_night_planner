<?php

namespace App\Policies;

use App\Models\GameSession;
use App\Models\User;

class GameSessionPolicy
{
    /**
     * Completed sessions are read-only.
     */
    public function update(User $user, GameSession $session): bool
    {
        return $session->user_id === $user->id && ! $session->isCompleted();
    }

    public function delete(User $user, GameSession $session): bool
    {
        return $session->user_id === $user->id && ! $session->isCompleted();
    }

    /**
     * Attendance can be changed until the session starts.
     */
    public function respond(User $user, GameSession $session): bool
    {
        return ! $session->hasStarted() && ! $session->isCompleted();
    }

    /**
     * Games can be suggested until the session starts.
     */
    public function suggestGame(User $user, GameSession $session): bool
    {
        return ! $session->hasStarted() && ! $session->isCompleted();
    }

    /**
     * The creator can complete their session once it has started, and only once.
     */
    public function complete(User $user, GameSession $session): bool
    {
        return $session->user_id === $user->id
            && $session->hasStarted()
            && ! $session->isCompleted();
    }

    /**
     * Only the creator can spin the game roulette, and only while the session is planned.
     */
    public function spin(User $user, GameSession $session): bool
    {
        return $session->user_id === $user->id && ! $session->isCompleted();
    }
}
