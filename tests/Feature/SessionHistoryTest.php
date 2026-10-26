<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Models\Game;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_sessions_page_shows_upcoming_and_completed_sessions(): void
    {
        GameSession::factory()->create(['title' => 'Next Friday']);
        GameSession::factory()->completed()->create(['title' => 'Last Friday']);

        $this->actingAs(User::factory()->create())
            ->get(route('sessions.index'))
            ->assertOk()
            ->assertSeeInOrder(['Upcoming', 'Next Friday', 'Completed', 'Last Friday']);
    }

    public function test_completed_sessions_are_listed_newest_first(): void
    {
        GameSession::factory()->completed()->create(['title' => 'Old Session', 'starts_at' => now()->subMonth()]);
        GameSession::factory()->completed()->create(['title' => 'Recent Session', 'starts_at' => now()->subDay()]);

        $this->actingAs(User::factory()->create())
            ->get(route('sessions.index'))
            ->assertSeeInOrder(['Recent Session', 'Old Session']);
    }

    public function test_completed_sessions_show_date_attendees_and_played_games(): void
    {
        $session = GameSession::factory()->completed()->create(['starts_at' => '2026-10-01 20:00']);
        $alice = User::factory()->create(['name' => 'Alice']);
        $bob = User::factory()->create(['name' => 'Bob']);
        $session->attendees()->attach([
            $alice->id => ['status' => AttendanceStatus::Going],
            $bob->id => ['status' => AttendanceStatus::NotGoing],
        ]);
        $session->games()->attach([
            Game::factory()->create(['name' => 'Mario Kart 8'])->id => ['was_played' => true],
            Game::factory()->create(['name' => 'Unplayed Game'])->id => ['was_played' => false],
        ]);

        $this->actingAs($alice)
            ->get(route('sessions.index'))
            ->assertSee('Thu 01 Oct 2026')
            ->assertSee('Attendees: Alice')
            ->assertDontSee('Bob')
            ->assertSeeInOrder(['Played · 1 of 2', 'Mario Kart 8'], false)
            ->assertDontSee('Unplayed Game');
    }

    public function test_completed_session_detail_separates_played_and_suggested_games(): void
    {
        $session = GameSession::factory()->completed()->create();
        $session->games()->attach([
            Game::factory()->create(['name' => 'Mario Kart 8'])->id => ['was_played' => true],
            Game::factory()->create(['name' => 'Unplayed Game'])->id => ['was_played' => false],
        ]);

        $this->actingAs($session->creator)
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSee('Cleared')
            ->assertSeeInOrder(['1 of 2 played', 'Mario Kart 8', 'Played', 'Unplayed Game', 'Not played'])
            ->assertDontSee('Edit session')
            ->assertDontSee('Delete session')
            ->assertDontSee('Complete session')
            ->assertDontSee('Suggest a game');
    }

    public function test_completed_sessions_are_excluded_from_upcoming_lists(): void
    {
        $session = GameSession::factory()->create(['title' => 'Finished Early']);
        $session->forceFill(['status' => 'completed'])->save();

        $this->assertSame(0, GameSession::upcoming()->count());
    }
}
