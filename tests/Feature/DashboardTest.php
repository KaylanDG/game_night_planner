<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_dashboard(): void
    {
        $user = User::factory()->create(['name' => 'Jane']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Welcome back, Jane');
    }

    public function test_guests_cannot_log_out(): void
    {
        $this->post('/logout')->assertRedirect(route('login'));
    }

    public function test_dashboard_handles_an_empty_group(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertOk()
            ->assertViewHas('totalSessions', 0)
            ->assertViewHas('gamesPlayed', 0)
            ->assertViewHas('nextSession', null)
            ->assertSee('No games played yet.')
            ->assertSee('No upcoming sessions');
    }

    public function test_dashboard_shows_session_and_game_statistics(): void
    {
        [$mario, $zelda, $tetris, $doom, $unplayed] = collect(['Mario Kart', 'Zelda', 'Tetris', 'Doom', 'Unplayed'])
            ->map(fn ($name) => Game::factory()->create(['name' => $name]));

        $completed = GameSession::factory()->completed()->count(3)->create();
        $completed[0]->games()->attach([$mario->id => ['was_played' => true], $zelda->id => ['was_played' => true], $unplayed->id => ['was_played' => false]]);
        $completed[1]->games()->attach([$mario->id => ['was_played' => true], $zelda->id => ['was_played' => true]]);
        $completed[2]->games()->attach([$mario->id => ['was_played' => true], $tetris->id => ['was_played' => true], $doom->id => ['was_played' => true]]);

        // Planned sessions, played games on them, don't count.
        GameSession::factory()->past()->create()->games()->attach([$doom->id => ['was_played' => true], $unplayed->id => ['was_played' => false]]);
        GameSession::factory()->create()->games()->attach([$unplayed->id => ['was_played' => false]]);

        $response = $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk();

        $response->assertViewHas('totalSessions', 3);
        $response->assertViewHas('gamesPlayed', 4);
        $response->assertViewHas('mostPlayedGames', fn ($games) => $games->pluck('name')->all() === ['Mario Kart', 'Zelda', 'Doom']
            && $games->pluck('played_sessions_count')->all() === [3, 2, 1]);
        $response->assertSeeInOrder(['Mario Kart', 'played in 3 sessions', 'Zelda', 'played in 2 sessions', 'Doom', 'played in 1 session']);
    }

    public function test_dashboard_shows_the_next_upcoming_session(): void
    {
        $next = GameSession::factory()->create(['title' => 'Soonest', 'starts_at' => now()->addDay()->setTime(19, 30)]);
        GameSession::factory()->create(['title' => 'Later', 'starts_at' => now()->addWeek()]);
        GameSession::factory()->past()->create(['title' => 'Past']);

        $going = User::factory()->count(2)->create();
        $next->attendees()->attach($going->pluck('id'), ['status' => AttendanceStatus::Going]);
        $next->attendees()->attach(User::factory()->create(), ['status' => AttendanceStatus::NotGoing]);

        $this->actingAs($going[0])
            ->get('/dashboard')
            ->assertViewHas('nextSession', fn ($session) => $session->is($next))
            ->assertSee('Soonest')
            ->assertSee($next->starts_at->format('l j F'))
            ->assertSee('19:30')
            ->assertSee('2 going')
            ->assertDontSee('Later');
    }

    public function test_dashboard_lists_group_members(): void
    {
        User::factory()->create(['name' => 'Charlie']);
        User::factory()->create(['name' => 'Alice']);
        $bob = User::factory()->create(['name' => 'Bob']);

        $this->actingAs($bob)
            ->get('/dashboard')
            ->assertViewHas('members', fn ($members) => $members->count() === 3)
            ->assertSeeInOrder(['Party members', '3', 'Alice', 'Bob', 'You', 'Charlie']);
    }
}
