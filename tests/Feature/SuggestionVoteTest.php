<?php

namespace Tests\Feature;

use App\Enums\Vote;
use App\Models\GameSession;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuggestionVoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_vote(): void
    {
        $suggestion = Suggestion::factory()->create();

        $this->put(route('suggestions.vote', $suggestion), ['vote' => 'like'])->assertRedirect(route('login'));

        $this->assertDatabaseCount('suggestion_votes', 0);
    }

    public function test_users_can_like_a_suggestion(): void
    {
        $suggestion = Suggestion::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('suggestions.vote', $suggestion), ['vote' => 'like'])
            ->assertRedirect(route('sessions.show', $suggestion->game_session_id)."#suggestion-{$suggestion->id}");

        $this->assertSame(Vote::Like, $suggestion->fresh()->voteBy($user));
    }

    public function test_users_can_dislike_a_suggestion(): void
    {
        $suggestion = Suggestion::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('suggestions.vote', $suggestion), ['vote' => 'dislike']);

        $this->assertSame(Vote::Dislike, $suggestion->fresh()->voteBy($user));
    }

    public function test_users_can_change_their_vote(): void
    {
        $suggestion = Suggestion::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('suggestions.vote', $suggestion), ['vote' => 'like']);
        $this->actingAs($user)->put(route('suggestions.vote', $suggestion), ['vote' => 'dislike']);

        $this->assertDatabaseCount('suggestion_votes', 1);
        $this->assertSame(Vote::Dislike, $suggestion->fresh()->voteBy($user));
    }

    public function test_repeating_a_vote_removes_it(): void
    {
        $suggestion = Suggestion::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('suggestions.vote', $suggestion), ['vote' => 'like']);
        $this->actingAs($user)->put(route('suggestions.vote', $suggestion), ['vote' => 'like']);

        $this->assertDatabaseCount('suggestion_votes', 0);
        $this->assertNull($suggestion->fresh()->voteBy($user));
    }

    public function test_database_prevents_duplicate_votes(): void
    {
        $suggestion = Suggestion::factory()->create();
        $user = User::factory()->create();
        $suggestion->votes()->create(['user_id' => $user->id, 'vote' => Vote::Like]);

        $this->expectException(UniqueConstraintViolationException::class);

        $suggestion->votes()->create(['user_id' => $user->id, 'vote' => Vote::Dislike]);
    }

    public function test_users_can_vote_on_their_own_suggestion(): void
    {
        $suggestion = Suggestion::factory()->create();

        $this->actingAs($suggestion->suggester)
            ->put(route('suggestions.vote', $suggestion), ['vote' => 'like'])
            ->assertRedirect();

        $this->assertSame(Vote::Like, $suggestion->fresh()->voteBy($suggestion->suggester));
    }

    public function test_vote_value_is_validated(): void
    {
        $suggestion = Suggestion::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('suggestions.vote', $suggestion), ['vote' => 'love'])->assertSessionHasErrors('vote');
        $this->actingAs($user)->put(route('suggestions.vote', $suggestion), [])->assertSessionHasErrors('vote');

        $this->assertDatabaseCount('suggestion_votes', 0);
    }

    public function test_voting_is_disabled_on_completed_sessions(): void
    {
        $suggestion = Suggestion::factory()->for(GameSession::factory()->completed())->create();

        $this->actingAs(User::factory()->create())
            ->put(route('suggestions.vote', $suggestion), ['vote' => 'like'])
            ->assertForbidden();

        $this->assertDatabaseCount('suggestion_votes', 0);
    }

    public function test_session_page_shows_vote_totals_and_the_users_vote(): void
    {
        $suggestion = Suggestion::factory()->create();
        $game = $suggestion->game->name;
        $me = User::factory()->create();

        $suggestion->votes()->createMany([
            ['user_id' => $me->id, 'vote' => Vote::Like],
            ['user_id' => User::factory()->create()->id, 'vote' => Vote::Like],
            ['user_id' => User::factory()->create()->id, 'vote' => Vote::Dislike],
        ]);

        $this->actingAs($me)
            ->get(route('sessions.show', $suggestion->game_session_id))
            ->assertOk()
            ->assertSeeInOrder(['aria-pressed="true"', "Like {$game} (2)"], false)
            ->assertSeeInOrder(['aria-pressed="false"', "Dislike {$game} (1)"], false);
    }

    public function test_vote_buttons_are_disabled_on_completed_sessions(): void
    {
        $suggestion = Suggestion::factory()->for(GameSession::factory()->completed())->create();

        $this->actingAs(User::factory()->create())
            ->get(route('sessions.show', $suggestion->game_session_id))
            ->assertSee('Voting is closed');
    }
}
