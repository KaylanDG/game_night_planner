<?php

namespace App\Models;

use App\Enums\SessionStatus;
use App\Services\IgdbService;
use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['igdb_id', 'name', 'cover_image_id', 'release_date', 'summary'])]
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_date' => 'date',
        ];
    }

    /**
     * @return BelongsToMany<GameSession, $this>
     */
    public function gameSessions(): BelongsToMany
    {
        return $this->belongsToMany(GameSession::class, 'game_session')
            ->using(Suggestion::class)
            ->withPivot('id', 'suggested_by', 'was_played')
            ->withTimestamps();
    }

    /**
     * Completed sessions in which this game was played.
     *
     * @return BelongsToMany<GameSession, $this>
     */
    public function playedSessions(): BelongsToMany
    {
        return $this->gameSessions()
            ->wherePivot('was_played', true)
            ->where('game_sessions.status', SessionStatus::Completed);
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn () => IgdbService::coverUrl($this->cover_image_id));
    }
}
