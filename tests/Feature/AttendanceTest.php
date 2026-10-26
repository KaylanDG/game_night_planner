<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Models\GameSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_mark_themselves_as_going(): void
    {
        $session = GameSession::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('sessions.attendance.update', $session), ['status' => 'going'])
            ->assertRedirect(route('sessions.show', $session));

        $this->assertSame(AttendanceStatus::Going, $session->attendanceStatusFor($user));
    }

    public function test_users_can_change_their_response_without_duplicating_it(): void
    {
        $session = GameSession::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('sessions.attendance.update', $session), ['status' => 'going']);
        $this->actingAs($user)->put(route('sessions.attendance.update', $session), ['status' => 'not_going']);

        $this->assertDatabaseCount('game_session_user', 1);
        $this->assertDatabaseHas('game_session_user', [
            'game_session_id' => $session->id,
            'user_id' => $user->id,
            'status' => 'not_going',
        ]);
    }

    public function test_status_must_be_valid(): void
    {
        $session = GameSession::factory()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('sessions.attendance.update', $session), ['status' => 'maybe'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseCount('game_session_user', 0);
    }

    public function test_users_cannot_respond_after_the_session_has_started(): void
    {
        $session = GameSession::factory()->past()->create();

        $this->actingAs(User::factory()->create())
            ->put(route('sessions.attendance.update', $session), ['status' => 'going'])
            ->assertForbidden();

        $this->assertDatabaseCount('game_session_user', 0);
    }

    public function test_show_page_lists_each_users_attendance_status(): void
    {
        $session = GameSession::factory()->create();
        $going = User::factory()->create(['name' => 'Alice']);
        $notGoing = User::factory()->create(['name' => 'Bob']);
        User::factory()->create(['name' => 'Carol']);

        $session->attendees()->attach([
            $going->id => ['status' => AttendanceStatus::Going],
            $notGoing->id => ['status' => AttendanceStatus::NotGoing],
        ]);

        $this->actingAs($going)
            ->get(route('sessions.show', $session))
            ->assertOk()
            ->assertSeeInOrder(['Going · 1', 'Alice (you)', "Can't make it · 1", 'Bob', 'Unconfirmed · 2', 'Carol']); // Carol and the session's creator
    }
}
