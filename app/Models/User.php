<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Sessions this user created.
     *
     * @return HasMany<GameSession, $this>
     */
    public function createdSessions(): HasMany
    {
        return $this->hasMany(GameSession::class);
    }

    /**
     * Sessions this user has responded to, with their status on the pivot.
     *
     * @return BelongsToMany<GameSession, $this>
     */
    public function gameSessions(): BelongsToMany
    {
        return $this->belongsToMany(GameSession::class)
            ->using(Attendance::class)
            ->as('attendance')
            ->withPivot('status')
            ->withTimestamps();
    }
}
