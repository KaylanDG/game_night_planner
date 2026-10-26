<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesIgdb;
use Tests\TestCase;

class GameSuggestionTest extends TestCase
{
    use FakesIgdb, RefreshDatabase;

    public function test_users_can_add_a_game_to_an_upcoming_session(): void
    {
        $this->fakeIgdb([$this->igdbGame()]);
        $user = User::factory()->create();
        $session = GameSession::factory()->create();

        $this->actingAs($user)
            ->post(route('games.suggestions.store', 1942), ['session_id' => $session->id])
            ->assertRedirect(route('games.show', 1942))
            ->assertSessionHas('suggestedTo', ['id' => $session->id, 'title' => $session->title]);

        $game = Game::sole();
        $this->assertSame(1942, $game->igdb_id);
        $this->assertSame('The Witcher 3: Wild Hunt', $game->name);
        $this->assertSame('co1wyy', $game->cover_image_id);
        $this->assertSame('2015-05-19', $game->release_date->toDateString());
        $this->assertDatabaseHas('game_session', [
            'game_id' => $game->id,
            'game_session_id' => $session->id,
            'suggested_by' => $user->id,
        ]);
    }

    public function test_existing_games_are_reused_across_sessions(): void
    {
        $game = Game::factory()->create(['igdb_id' => 1942]);
        $sessions = GameSession::factory()->count(2)->create();
        $user = User::factory()->create();

        foreach ($sessions as $session) {
            $this->actingAs($user)->post(route('games.suggestions.store', 1942), ['session_id' => $session->id]);
        }

        Http::assertNothingSent();
        $this->assertDatabaseCount('games', 1);
        $this->assertSame(2, $game->gameSessions()->count());
    }

    public function test_same_game_cannot_be_added_to_a_session_twice(): void
    {
        $game = Game::factory()->create(['igdb_id' => 1942]);
        $session = GameSession::factory()->create();
        $session->games()->attach($game);

        $this->actingAs(User::factory()->create())
            ->post(route('games.suggestions.store', 1942), ['session_id' => $session->id])
            ->assertSessionHasErrors(['session_id' => 'This game has already been suggested for that session.']);

        $this->assertDatabaseCount('game_session', 1);
    }

    public function test_games_cannot_be_added_to_past_sessions(): void
    {
        $session = GameSession::factory()->past()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('games.suggestions.store', 1942), ['session_id' => $session->id])
            ->assertSessionHasErrors(['session_id' => 'Games can only be added to upcoming sessions.']);

        Http::assertNothingSent();
        $this->assertDatabaseCount('games', 0);
    }

    public function test_session_selection_is_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('games.suggestions.store', 1942), [])
            ->assertSessionHasErrors('session_id');

        $this->actingAs($user)
            ->post(route('games.suggestions.store', 1942), ['session_id' => 999])
            ->assertSessionHasErrors('session_id');

        $this->actingAs($user)
            ->post(route('games.suggestions.store', 1942), ['session_id' => 'abc'])
            ->assertSessionHasErrors('session_id');

        $this->assertDatabaseCount('game_session', 0);
    }

    public function test_unknown_igdb_game_cannot_be_added(): void
    {
        $this->fakeIgdb([]);
        $session = GameSession::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('games.suggestions.store', 999999), ['session_id' => $session->id])
            ->assertNotFound();

        $this->assertDatabaseCount('games', 0);
    }

    public function test_igdb_failure_while_adding_shows_an_error(): void
    {
        Http::fake([
            'id.twitch.tv/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
            'api.igdb.com/*' => Http::response('Server error', 500),
        ]);
        $session = GameSession::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('games.suggestions.store', 1942), ['session_id' => $session->id])
            ->assertSessionHasErrors('session_id');

        $this->assertDatabaseCount('games', 0);
    }

    public function test_suggested_games_appear_on_the_session_page(): void
    {
        $suggester = User::factory()->create(['name' => 'Alice']);
        $session = GameSession::factory()->create();
        $game = Game::factory()->create(['name' => 'Rocket League', 'cover_image_id' => 'co5w0w']);
        $session->games()->attach($game, ['suggested_by' => $suggester->id]);

        $this->actingAs(User::factory()->create())
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('Rocket League')
            ->assertSee('suggested by Alice')
            ->assertSee('t_cover_small/co5w0w.jpg');
    }
}
