<?php

namespace Database\Factories;

use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GameSession>
 */
class GameSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => GameSession::DEFAULT_TITLE,
            'starts_at' => now()->addDays(fake()->numberBetween(1, 30))->setTime(20, 0),
        ];
    }

    /**
     * Indicate that the session has already started.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the session took place and was completed.
     */
    public function completed(): static
    {
        return $this->past()->state(fn (array $attributes) => [
            'status' => SessionStatus::Completed,
        ]);
    }
}
