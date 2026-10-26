<?php

namespace Tests\Feature;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesIgdb;
use Tests\TestCase;

class GameCatalogueTest extends TestCase
{
    use FakesIgdb, RefreshDatabase;

    public function test_guests_cannot_access_game_pages(): void
    {
        $this->get(route('games.index'))->assertRedirect(route('login'));
        $this->get(route('games.show', 1942))->assertRedirect(route('login'));
        $this->post(route('games.suggestions.store', 1942))->assertRedirect(route('login'));
    }

    public function test_popular_games_are_shown_without_a_search(): void
    {
        $this->fakeIgdb([$this->igdbGame()]);

        $this->actingAs(User::factory()->create())
            ->get(route('games.index'))
            ->assertOk()
            ->assertSee('Popular right now')
            ->assertSee('The Witcher 3: Wild Hunt')
            ->assertSee('2015')
            ->assertSee('https://images.igdb.com/igdb/image/upload/t_cover_big/co1wyy.jpg');

        Http::assertSent(fn (Request $request) => str_contains($request->url(), 'api.igdb.com')
            && str_contains($request->body(), 'sort total_rating_count desc'));
    }

    public function test_users_can_search_games(): void
    {
        $this->fakeIgdb([$this->igdbGame()]);

        $this->actingAs(User::factory()->create())
            ->get(route('games.index', ['search' => 'witcher']))
            ->assertOk()
            ->assertSee('Results for &quot;witcher&quot;', false)
            ->assertSee('The Witcher 3: Wild Hunt');

        Http::assertSent(fn (Request $request) => str_contains($request->body(), 'search "witcher";'));
    }

    public function test_empty_search_results_show_a_message(): void
    {
        $this->fakeIgdb([]);

        $this->actingAs(User::factory()->create())
            ->get(route('games.index', ['search' => 'zzzzzz']))
            ->assertOk()
            ->assertSee('No games found for “zzzzzz”', false);
    }

    public function test_pagination_requests_the_next_offset(): void
    {
        $games = array_map(fn ($id) => $this->igdbGame(['id' => $id, 'name' => "Game {$id}"]), range(1, 25));
        $this->fakeIgdb($games);

        $this->actingAs(User::factory()->create())
            ->get(route('games.index', ['search' => 'game', 'page' => 2]))
            ->assertOk()
            ->assertSee('Previous')
            ->assertSee('Next')
            ->assertSee('Game 24')
            ->assertDontSee('Game 25');

        Http::assertSent(fn (Request $request) => str_contains($request->body(), 'limit 25; offset 24;'));
    }

    public function test_catalogue_shows_an_error_when_igdb_is_unavailable(): void
    {
        Http::fake([
            'id.twitch.tv/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
            'api.igdb.com/*' => Http::response('Server error', 500),
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('games.index'))
            ->assertOk()
            ->assertSee('The game catalogue is currently unavailable.');
    }

    public function test_game_detail_page_shows_game_information(): void
    {
        $this->fakeIgdb([$this->igdbGame()]);
        $upcoming = GameSession::factory()->create(['title' => 'Friday Night']);
        GameSession::factory()->past()->create(['title' => 'Last Week']);

        $this->actingAs(User::factory()->create())
            ->get(route('games.show', 1942))
            ->assertOk()
            ->assertSee('The Witcher 3: Wild Hunt')
            ->assertSee('19 May 2015')
            ->assertSee('RPG and sequel to The Witcher 2.')
            ->assertSeeInOrder(['Genres', 'Role-playing (RPG)', 'Adventure'])
            ->assertSeeInOrder(['Platforms', 'PC (Microsoft Windows)', 'PlayStation 4'])
            ->assertSee('co1wyy.jpg')
            ->assertSee('Add to session')
            ->assertSee('Friday Night')
            ->assertDontSee('Last Week');

        Http::assertSent(fn (Request $request) => str_contains($request->body(), 'where id = 1942;'));
    }

    public function test_unknown_game_returns_404(): void
    {
        $this->fakeIgdb([]);

        $this->actingAs(User::factory()->create())
            ->get(route('games.show', 999999))
            ->assertNotFound();
    }

    public function test_non_numeric_game_id_returns_404(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/games/not-a-number')
            ->assertNotFound();
    }

    public function test_game_detail_shows_an_error_when_igdb_is_unavailable(): void
    {
        Http::fake(['id.twitch.tv/*' => fn () => throw new ConnectionException('Timed out')]);

        $this->actingAs(User::factory()->create())
            ->get(route('games.show', 1942))
            ->assertOk()
            ->assertSee('Game unavailable');
    }
}
