<?php

namespace Tests\Feature;

use App\Enums\Vote;
use App\Models\GameSession;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuggestionRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_remove_suggestions(): void
    {
        $suggestion = Suggestion::factory()->create();

        $this->delete(route('suggestions.destroy', $suggestion))->assertRedirect(route('login'));

        $this->assertModelExists($suggestion);
    }

    public function test_suggester_can_remove_their_suggestion(): void
    {
        $suggestion = Suggestion::factory()->create();
        $suggestion->votes()->create(['user_id' => User::factory()->create()->id, 'vote' => Vote::Like]);

        $this->actingAs($suggestion->suggester)
            ->delete(route('suggestions.destroy', $suggestion))
            ->assertRedirect(route('sessions.show', $suggestion->game_session_id));

        $this->assertModelMissing($suggestion);
        $this->assertModelExists($suggestion->game);
        $this->assertDatabaseCount('suggestion_votes', 0);
    }

    public function test_other_users_cannot_remove_a_suggestion(): void
    {
        $suggestion = Suggestion::factory()->create();

        // Not even the session's creator.
        foreach ([User::factory()->create(), $suggestion->gameSession->creator] as $user) {
            $this->actingAs($user)
                ->delete(route('suggestions.destroy', $suggestion))
                ->assertForbidden();
        }

        $this->assertModelExists($suggestion);
    }

    public function test_suggestions_cannot_be_removed_from_completed_sessions(): void
    {
        $suggestion = Suggestion::factory()
            ->for(GameSession::factory()->completed())
            ->create();

        $this->actingAs($suggestion->suggester)
            ->delete(route('suggestions.destroy', $suggestion))
            ->assertForbidden();

        $this->assertModelExists($suggestion);
    }

    public function test_remove_button_is_only_shown_to_the_suggester(): void
    {
        $suggestion = Suggestion::factory()->create();
        $label = 'Remove '.$suggestion->game->name;

        $this->actingAs($suggestion->suggester)
            ->get(route('sessions.show', $suggestion->game_session_id))
            ->assertSee($label);

        $this->actingAs(User::factory()->create())
            ->get(route('sessions.show', $suggestion->game_session_id))
            ->assertDontSee($label);
    }
}
