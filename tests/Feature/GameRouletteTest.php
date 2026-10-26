<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameSession;
use App\Models\Suggestion;
use App\Models\User;
use App\Services\GameRoulette;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Random\Engine\Mt19937;
use Random\Randomizer;
use Tests\TestCase;

class GameRouletteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_spin(): void
    {
        $session = GameSession::factory()->create();

        $this->post(route('sessions.roulette', $session))->assertRedirect(route('login'));
        $this->postJson(route('sessions.roulette', $session))->assertUnauthorized();
    }

    public function test_creator_spins_and_gets_one_of_the_sessions_suggestions(): void
    {
        $session = GameSession::factory()->create();
        $suggestions = Suggestion::factory()->count(3)->for($session)->create();

        $response = $this->actingAs($session->creator)
            ->postJson(route('sessions.roulette', $session))
            ->assertOk()
            ->assertJsonStructure(['suggestion_id', 'game_id', 'igdb_id', 'name', 'cover_url']);

        $winner = $suggestions->firstWhere('id', $response->json('suggestion_id'));
        $this->assertNotNull($winner);
        $response->assertJson([
            'game_id' => $winner->game->id,
            'igdb_id' => $winner->game->igdb_id,
            'name' => $winner->game->name,
            'cover_url' => $winner->game->cover_url,
        ]);
    }

    public function test_other_users_cannot_spin(): void
    {
        $session = GameSession::factory()->create();
        Suggestion::factory()->for($session)->create();

        $this->actingAs(User::factory()->create())
            ->postJson(route('sessions.roulette', $session))
            ->assertForbidden();
    }

    public function test_completed_sessions_cannot_be_spun(): void
    {
        $session = GameSession::factory()->completed()->create();
        Suggestion::factory()->for($session)->create();

        $this->actingAs($session->creator)
            ->postJson(route('sessions.roulette', $session))
            ->assertForbidden();
    }

    public function test_started_planned_sessions_can_still_be_spun(): void
    {
        $session = GameSession::factory()->past()->create();
        Suggestion::factory()->for($session)->create();

        $this->actingAs($session->creator)
            ->postJson(route('sessions.roulette', $session))
            ->assertOk();
    }

    public function test_spinning_a_session_without_suggestions_fails_gracefully(): void
    {
        $session = GameSession::factory()->create();

        $this->actingAs($session->creator)
            ->postJson(route('sessions.roulette', $session))
            ->assertUnprocessable()
            ->assertJson(['message' => 'There are no suggested games to spin.']);
    }

    public function test_spinning_stores_nothing_and_does_not_mark_games_played(): void
    {
        $session = GameSession::factory()->create();
        Suggestion::factory()->count(2)->for($session)->create();
        $before = Suggestion::orderBy('id')->get()->toArray();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($session->creator)->postJson(route('sessions.roulette', $session))->assertOk();
        }

        $this->assertSame($before, Suggestion::orderBy('id')->get()->toArray());
        $this->assertSame(0, $session->playedGames()->count());
    }

    public function test_only_the_sessions_own_suggestions_are_on_the_wheel(): void
    {
        $session = GameSession::factory()->create();
        $mine = Suggestion::factory()->for($session)->create();
        Suggestion::factory()->count(3)->create();

        for ($i = 0; $i < 10; $i++) {
            $this->assertTrue((new GameRoulette)->spin($session)->is($mine));
        }
    }

    public function test_selection_is_uniform_and_ignores_votes(): void
    {
        $session = GameSession::factory()->create();
        $suggestions = Suggestion::factory()->count(4)->for($session)->create();

        // Heavily disliked and liked games must not change the odds.
        $suggestions[0]->votes()->createMany(User::factory()->count(5)->create()->map(fn ($u) => ['user_id' => $u->id, 'vote' => 'dislike']));
        $suggestions[1]->votes()->createMany(User::factory()->count(5)->create()->map(fn ($u) => ['user_id' => $u->id, 'vote' => 'like']));

        // Seeded so the test is deterministic.
        $roulette = new GameRoulette(new Randomizer(new Mt19937(1234)));
        $counts = array_fill_keys($suggestions->pluck('id')->all(), 0);

        for ($i = 0; $i < 4000; $i++) {
            $counts[$roulette->spin($session)->id]++;
        }

        // Expect ~1000 each; allow generous tolerance (±15%).
        foreach ($counts as $count) {
            $this->assertGreaterThan(850, $count);
            $this->assertLessThan(1150, $count);
        }
    }

    public function test_spin_button_and_wheel_are_shown_only_to_the_creator(): void
    {
        $session = GameSession::factory()->create();
        Suggestion::factory()->for($session)->for(Game::factory()->state(['name' => 'Rocket League']))->create();

        $this->actingAs($session->creator)
            ->get(route('sessions.show', $session))
            ->assertSee('data-dialog-open="roulette"', false)
            ->assertSee('data-roulette="'.route('sessions.roulette', $session).'"', false)
            ->assertSee('Rocket League');

        $this->actingAs(User::factory()->create())
            ->get(route('sessions.show', $session))
            ->assertDontSee('data-dialog-open="roulette"', false)
            ->assertDontSee('data-roulette=', false);
    }

    public function test_wheel_shows_empty_state_without_suggestions(): void
    {
        $session = GameSession::factory()->create();

        $this->actingAs($session->creator)
            ->get(route('sessions.show', $session))
            ->assertSee('Nothing to spin')
            ->assertDontSee('data-roulette-spin', false);
    }

    public function test_spin_button_is_hidden_on_completed_sessions(): void
    {
        $session = GameSession::factory()->completed()->create();
        Suggestion::factory()->for($session)->create();

        $this->actingAs($session->creator)
            ->get(route('sessions.show', $session))
            ->assertDontSee('data-dialog-open="roulette"', false);
    }
}
