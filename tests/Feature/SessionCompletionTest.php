<?php

namespace Tests\Feature;

use App\Enums\SessionStatus;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_complete_sessions(): void
    {
        $session = GameSession::factory()->past()->create();

        $this->get(route('sessions.complete.create', $session))->assertRedirect(route('login'));
        $this->post(route('sessions.complete.store', $session))->assertRedirect(route('login'));
    }

    public function test_creator_can_view_the_completion_form_with_suggested_games(): void
    {
        $session = GameSession::factory()->past()->create();
        $session->games()->attach(Game::factory()->create(['name' => 'Overcooked 2']));

        $this->actingAs($session->creator)
            ->get(route('sessions.complete.create', $session))
            ->assertOk()
            ->assertSee('Overcooked 2');
    }

    public function test_creator_can_complete_a_started_session_and_record_played_games(): void
    {
        $session = GameSession::factory()->past()->create();
        [$played, $alsoPlayed, $skipped] = Game::factory()->count(3)->create();
        $session->games()->attach([$played->id, $alsoPlayed->id, $skipped->id]);

        $this->actingAs($session->creator)
            ->post(route('sessions.complete.store', $session), [
                'played_games' => [$played->id, $alsoPlayed->id],
            ])
            ->assertRedirect(route('sessions.show', $session));

        $this->assertSame(SessionStatus::Completed, $session->fresh()->status);
        $this->assertEqualsCanonicalizing(
            [$played->id, $alsoPlayed->id],
            $session->playedGames()->pluck('games.id')->all(),
        );
        $this->assertDatabaseHas('game_session', [
            'game_session_id' => $session->id,
            'game_id' => $skipped->id,
            'was_played' => false,
        ]);
    }

    public function test_session_can_be_completed_without_any_played_games(): void
    {
        $session = GameSession::factory()->past()->create();
        $session->games()->attach(Game::factory()->create());

        $this->actingAs($session->creator)
            ->post(route('sessions.complete.store', $session))
            ->assertRedirect(route('sessions.show', $session));

        $this->assertTrue($session->fresh()->isCompleted());
        $this->assertSame(0, $session->playedGames()->count());
    }

    public function test_selected_games_must_belong_to_the_session(): void
    {
        $session = GameSession::factory()->past()->create();
        $session->games()->attach($suggested = Game::factory()->create());
        $other = Game::factory()->create();
        GameSession::factory()->create()->games()->attach($other);

        $this->actingAs($session->creator)
            ->post(route('sessions.complete.store', $session), [
                'played_games' => [$suggested->id, $other->id],
            ])
            ->assertSessionHasErrors('played_games.1');

        $this->assertFalse($session->fresh()->isCompleted());
        $this->assertSame(0, $session->playedGames()->count());
    }

    public function test_played_games_input_is_validated(): void
    {
        $session = GameSession::factory()->past()->create();

        $this->actingAs($session->creator)
            ->post(route('sessions.complete.store', $session), ['played_games' => 'not-an-array'])
            ->assertSessionHasErrors('played_games');

        $this->actingAs($session->creator)
            ->post(route('sessions.complete.store', $session), ['played_games' => ['abc']])
            ->assertSessionHasErrors('played_games.0');

        $this->assertFalse($session->fresh()->isCompleted());
    }

    public function test_only_the_creator_can_complete_a_session(): void
    {
        $session = GameSession::factory()->past()->create();
        $other = User::factory()->create();

        $this->actingAs($other)->get(route('sessions.complete.create', $session))->assertForbidden();
        $this->actingAs($other)->post(route('sessions.complete.store', $session))->assertForbidden();

        $this->assertFalse($session->fresh()->isCompleted());
    }

    public function test_session_cannot_be_completed_before_it_starts(): void
    {
        $session = GameSession::factory()->create();

        $this->actingAs($session->creator)
            ->post(route('sessions.complete.store', $session))
            ->assertForbidden();

        $this->assertFalse($session->fresh()->isCompleted());
    }

    public function test_completed_session_cannot_be_completed_again(): void
    {
        $session = GameSession::factory()->completed()->create();
        $session->games()->attach($game = Game::factory()->create());

        $this->actingAs($session->creator)->get(route('sessions.complete.create', $session))->assertForbidden();
        $this->actingAs($session->creator)
            ->post(route('sessions.complete.store', $session), ['played_games' => [$game->id]])
            ->assertForbidden();

        $this->assertSame(0, $session->playedGames()->count());
    }

    public function test_completed_sessions_are_read_only(): void
    {
        $session = GameSession::factory()->completed()->create(['title' => 'Done']);
        $creator = $session->creator;

        $this->actingAs($creator)->get(route('sessions.edit', $session))->assertForbidden();
        $this->actingAs($creator)
            ->put(route('sessions.update', $session), [
                'title' => 'Changed',
                'date' => now()->addDay()->format('Y-m-d'),
                'start_time' => '20:00',
            ])
            ->assertForbidden();
        $this->actingAs($creator)->delete(route('sessions.destroy', $session))->assertForbidden();
        $this->actingAs($creator)
            ->put(route('sessions.attendance.update', $session), ['status' => 'going'])
            ->assertForbidden();
        $this->actingAs($creator)
            ->post(route('games.suggestions.store', 1942), ['session_id' => $session->id])
            ->assertSessionHasErrors('session_id');

        $this->assertModelExists($session);
        $this->assertSame('Done', $session->fresh()->title);
        $this->assertDatabaseCount('game_session_user', 0);
    }

    public function test_past_planned_session_remains_accessible_for_completion(): void
    {
        $session = GameSession::factory()->past()->create();

        $this->actingAs($session->creator)
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('Awaiting completion')
            ->assertSee('Complete session');

        $this->actingAs(User::factory()->create())
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertDontSee('Complete session');
    }
}
