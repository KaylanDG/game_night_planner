<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Http;

trait FakesIgdb
{
    protected function setUpFakesIgdb(): void
    {
        config([
            'services.igdb.client_id' => 'test-client-id',
            'services.igdb.secret' => 'test-secret',
        ]);

        Http::preventStrayRequests();
    }

    /**
     * Fake Twitch auth and answer every IGDB query with the given games.
     *
     * @param  list<array<string, mixed>>  $games
     */
    protected function fakeIgdb(array $games = []): void
    {
        Http::fake([
            'id.twitch.tv/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 5000000]),
            'api.igdb.com/*' => Http::response($games),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function igdbGame(array $overrides = []): array
    {
        return array_merge([
            'id' => 1942,
            'name' => 'The Witcher 3: Wild Hunt',
            'cover' => ['id' => 1, 'image_id' => 'co1wyy'],
            'first_release_date' => 1431993600, // 2015-05-19
            'summary' => 'RPG and sequel to The Witcher 2.',
            'genres' => [['id' => 12, 'name' => 'Role-playing (RPG)'], ['id' => 31, 'name' => 'Adventure']],
            'platforms' => [['id' => 6, 'name' => 'PC (Microsoft Windows)'], ['id' => 48, 'name' => 'PlayStation 4']],
        ], $overrides);
    }
}
