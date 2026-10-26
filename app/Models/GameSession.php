<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use App\Enums\SessionStatus;
use Database\Factories\GameSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'starts_at'])]
class GameSession extends Model
{
    /** @use HasFactory<GameSessionFactory> */
    use HasFactory;

    public const DEFAULT_TITLE = 'Game Night';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'planned',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'status' => SessionStatus::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Users who have responded to this session, with their status on the pivot.
     *
     * @return BelongsToMany<User, $this>
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(Attendance::class)
            ->as('attendance')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Users who said they're going.
     *
     * @return BelongsToMany<User, $this>
     */
    public function goingAttendees(): BelongsToMany
    {
        return $this->attendees()->wherePivot('status', AttendanceStatus::Going->value);
    }

    /**
     * Games suggested for this session.
     *
     * @return BelongsToMany<Game, $this>
     */
    public function games(): BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'game_session')
            ->using(Suggestion::class)
            ->withPivot('id', 'suggested_by', 'was_played')
            ->withTimestamps();
    }

    /**
     * Suggestions for this session, each with its game, suggester and votes available.
     *
     * @return HasMany<Suggestion, $this>
     */
    public function suggestions(): HasMany
    {
        return $this->hasMany(Suggestion::class);
    }

    /**
     * Suggested games that were actually played.
     *
     * @return BelongsToMany<Game, $this>
     */
    public function playedGames(): BelongsToMany
    {
        return $this->games()->wherePivot('was_played', true);
    }

    /**
     * Planned sessions that haven't started yet, soonest first.
     *
     * @param  Builder<GameSession>  $query
     */
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('status', SessionStatus::Planned)
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at');
    }

    /**
     * Planned sessions that have started but haven't been completed yet.
     *
     * @param  Builder<GameSession>  $query
     */
    public function scopeAwaitingCompletion(Builder $query): void
    {
        $query->where('status', SessionStatus::Planned)
            ->where('starts_at', '<', now())
            ->orderByDesc('starts_at');
    }

    /**
     * Completed sessions, most recent first.
     *
     * @param  Builder<GameSession>  $query
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', SessionStatus::Completed)->orderByDesc('starts_at');
    }

    public function hasStarted(): bool
    {
        return $this->starts_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === SessionStatus::Completed;
    }

    /**
     * Split the given users by their response to this session.
     *
     * @param  Collection<int, User>  $users
     * @return array{going: Collection<int, User>, not_going: Collection<int, User>, unconfirmed: Collection<int, User>}
     */
    public function attendanceGroups(Collection $users): array
    {
        $groups = $users->groupBy(fn (User $user) => $this->attendanceStatusFor($user)?->value ?? 'unconfirmed');

        return [
            'going' => $groups->get(AttendanceStatus::Going->value, new Collection),
            'not_going' => $groups->get(AttendanceStatus::NotGoing->value, new Collection),
            'unconfirmed' => $groups->get('unconfirmed', new Collection),
        ];
    }

    /**
     * The user's response, or null when they haven't responded (unconfirmed).
     */
    public function attendanceStatusFor(User $user): ?AttendanceStatus
    {
        return $this->attendees->firstWhere('id', $user->id)?->attendance->status;
    }
}
