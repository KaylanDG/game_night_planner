<?php

namespace Tests\Feature;

use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_session_routes(): void
    {
        $session = GameSession::factory()->create();

        $this->get(route('sessions.index'))->assertRedirect(route('login'));
        $this->get(route('sessions.create'))->assertRedirect(route('login'));
        $this->post(route('sessions.store'))->assertRedirect(route('login'));
        $this->get(route('sessions.show', $session))->assertRedirect(route('login'));
        $this->get(route('sessions.edit', $session))->assertRedirect(route('login'));
        $this->put(route('sessions.update', $session))->assertRedirect(route('login'));
        $this->delete(route('sessions.destroy', $session))->assertRedirect(route('login'));
        $this->put(route('sessions.attendance.update', $session))->assertRedirect(route('login'));
    }

    public function test_index_lists_upcoming_sessions_in_order_and_past_planned_separately(): void
    {
        GameSession::factory()->create(['title' => 'Later', 'starts_at' => now()->addWeek()]);
        GameSession::factory()->create(['title' => 'Sooner', 'starts_at' => now()->addDay()]);
        GameSession::factory()->past()->create(['title' => 'Already happened']);

        $this->actingAs(User::factory()->create())
            ->get(route('sessions.index'))
            ->assertOk()
            ->assertSeeInOrder(['Upcoming', 'Sooner', 'Later', 'Awaiting completion', 'Already happened']);
    }

    public function test_users_can_create_a_session(): void
    {
        $user = User::factory()->create();
        $date = now()->addDays(3);

        $response = $this->actingAs($user)->post(route('sessions.store'), [
            'title' => 'Board Game Bash',
            'date' => $date->format('Y-m-d'),
            'start_time' => '19:30',
        ]);

        $session = GameSession::sole();
        $response->assertRedirect(route('sessions.show', $session));
        $this->assertSame('Board Game Bash', $session->title);
        $this->assertSame($date->format('Y-m-d').' 19:30', $session->starts_at->format('Y-m-d H:i'));
        $this->assertTrue($session->creator->is($user));
    }

    public function test_title_defaults_to_game_night(): void
    {
        $this->actingAs(User::factory()->create())->post(route('sessions.store'), [
            'title' => '',
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '20:00',
        ]);

        $this->assertSame('Game Night', GameSession::sole()->title);
    }

    public function test_session_input_is_validated(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('sessions.store'), [
                'title' => str_repeat('a', 256),
                'date' => 'tomorrow',
                'start_time' => '25:00',
            ])
            ->assertSessionHasErrors(['title', 'date', 'start_time']);

        $this->assertDatabaseCount('game_sessions', 0);
    }

    public function test_session_cannot_be_scheduled_in_the_past(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('sessions.store'), [
                'date' => now()->subDay()->format('Y-m-d'),
                'start_time' => '20:00',
            ])
            ->assertSessionHasErrors('date');

        $this->assertDatabaseCount('game_sessions', 0);
    }

    public function test_any_user_can_view_a_session(): void
    {
        $session = GameSession::factory()->create(['title' => 'Mario Kart Night']);

        $this->actingAs(User::factory()->create())
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('Mario Kart Night')
            ->assertSee($session->creator->name)
            ->assertDontSee('Edit session');
    }

    public function test_creator_can_edit_and_update_their_session(): void
    {
        $session = GameSession::factory()->create();
        $date = now()->addDays(5);

        $this->actingAs($session->creator)
            ->get(route('sessions.edit', $session))
            ->assertOk();

        $this->actingAs($session->creator)
            ->put(route('sessions.update', $session), [
                'title' => 'Renamed',
                'date' => $date->format('Y-m-d'),
                'start_time' => '18:00',
            ])
            ->assertRedirect(route('sessions.show', $session));

        $session->refresh();
        $this->assertSame('Renamed', $session->title);
        $this->assertSame($date->format('Y-m-d').' 18:00', $session->starts_at->format('Y-m-d H:i'));
    }

    public function test_other_users_cannot_edit_or_update_a_session(): void
    {
        $session = GameSession::factory()->create(['title' => 'Original']);
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('sessions.edit', $session))->assertForbidden();

        $this->actingAs($other)
            ->put(route('sessions.update', $session), [
                'title' => 'Hijacked',
                'date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '20:00',
            ])
            ->assertForbidden();

        $this->assertSame('Original', $session->fresh()->title);
    }

    public function test_creator_can_delete_their_session(): void
    {
        $session = GameSession::factory()->create();

        $this->actingAs($session->creator)
            ->delete(route('sessions.destroy', $session))
            ->assertRedirect(route('sessions.index'));

        $this->assertModelMissing($session);
    }

    public function test_other_users_cannot_delete_a_session(): void
    {
        $session = GameSession::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('sessions.destroy', $session))
            ->assertForbidden();

        $this->assertModelExists($session);
    }
}
