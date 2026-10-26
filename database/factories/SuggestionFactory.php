<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameSession;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Suggestion>
 */
class SuggestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'game_session_id' => GameSession::factory(),
            'suggested_by' => User::factory(),
            'was_played' => false,
        ];
    }
}
