<?php

namespace App\Models;

use App\Enums\Vote;
use Database\Factories\SuggestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * A game suggested for a session (a row of the game_session pivot table).
 */
class Suggestion extends Pivot
{
    /** @use HasFactory<SuggestionFactory> */
    use HasFactory;

    protected $table = 'game_session';

    public $incrementing = true;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'was_played' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Game, $this>
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * @return BelongsTo<GameSession, $this>
     */
    public function gameSession(): BelongsTo
    {
        return $this->belongsTo(GameSession::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function suggester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suggested_by');
    }

    /**
     * @return HasMany<SuggestionVote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(SuggestionVote::class, 'suggestion_id');
    }

    public function voteCount(Vote $vote): int
    {
        return $this->votes->where('vote', $vote)->count();
    }

    public function voteBy(User $user): ?Vote
    {
        return $this->votes->firstWhere('user_id', $user->id)?->vote;
    }
}
