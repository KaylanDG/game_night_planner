<?php

namespace App\Policies;

use App\Models\Suggestion;
use App\Models\User;

class SuggestionPolicy
{
    /**
     * Only the suggester can remove a suggestion, and not once the session is completed.
     */
    public function delete(User $user, Suggestion $suggestion): bool
    {
        return $suggestion->suggested_by === $user->id
            && ! $suggestion->gameSession->isCompleted();
    }

    /**
     * Anyone can vote, including on their own suggestions, until the session is completed.
     */
    public function vote(User $user, Suggestion $suggestion): bool
    {
        return ! $suggestion->gameSession->isCompleted();
    }
}
