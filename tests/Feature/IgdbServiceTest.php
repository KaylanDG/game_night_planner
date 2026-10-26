<?php

namespace Tests\Feature;

use App\Exceptions\IgdbException;
use App\Services\IgdbService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesIgdb;
use Tests\TestCase;

class IgdbServiceTest extends TestCase
{
    use FakesIgdb;

    public function test_access_token_is_requested_once_and_cached(): void
    {
        $this->fakeIgdb([$this->igdbGame()]);
        $igdb = app(IgdbService::class);

        $igdb->search('witcher');
        $igdb->search('zelda');

        Http::assertSentCount(3); // 1 token + 2 searches
        Http::assertSent(fn (Request $request) => $request->url() === 'https://id.twitch.tv/oauth2/token'
            && $request['client_id'] === 'test-client-id'
            && $request['client_secret'] === 'test-secret'
            && $request['grant_type'] === 'client_credentials');
        Http::assertSent(fn (Request $request) => str_starts_with($request->url(), 'https://api.igdb.com/v4/games')
            && $request->hasHeader('Client-ID', 'test-client-id')
            && $request->hasHeader('Authorization', 'Bearer test-token'));
        $this->assertSame('test-token', Cache::get('igdb.access_token'));
    }

    public function test_search_results_are_cached(): void
    {
        $this->fakeIgdb([$this->igdbGame()]);
        $igdb = app(IgdbService::class);

        $igdb->search('witcher');
        $igdb->search('witcher');

        Http::assertSentCount(2); // 1 token + 1 search
    }

    public function test_search_query_is_sent_to_igdb_and_normalized(): void
    {
        $this->fakeIgdb([$this->igdbGame()]);

        $result = app(IgdbService::class)->search('the "witcher"');

        Http::assertSent(fn (Request $request) => str_contains($request->body(), 'search "the witcher";'));
        $this->assertFalse($result['has_more']);
        $this->assertSame(1942, $result['games'][0]['igdb_id']);
        $this->assertSame('co1wyy', $result['games'][0]['cover_image_id']);
        $this->assertSame(2015, $result['games'][0]['release_year']);
    }

    public function test_expired_token_is_refreshed_and_request_retried(): void
    {
        Cache::put('igdb.access_token', 'stale-token', 3600);

        Http::fake([
            'id.twitch.tv/*' => Http::response(['access_token' => 'fresh-token', 'expires_in' => 3600]),
            'api.igdb.com/*' => Http::sequence()
                ->push(['message' => 'Unauthorized'], 401)
                ->push([$this->igdbGame()]),
        ]);

        $game = app(IgdbService::class)->find(1942);

        $this->assertSame('The Witcher 3: Wild Hunt', $game['name']);
        $this->assertSame('fresh-token', Cache::get('igdb.access_token'));
    }

    public function test_api_errors_raise_an_igdb_exception(): void
    {
        Http::fake([
            'id.twitch.tv/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
            'api.igdb.com/*' => Http::response('Server error', 500),
        ]);

        $this->expectException(IgdbException::class);

        app(IgdbService::class)->search('witcher');
    }

    public function test_timeouts_raise_an_igdb_exception(): void
    {
        Http::fake([
            'id.twitch.tv/*' => Http::response(['access_token' => 'test-token', 'expires_in' => 3600]),
            'api.igdb.com/*' => fn () => throw new ConnectionException('Timed out'),
        ]);

        $this->expectException(IgdbException::class);

        app(IgdbService::class)->search('witcher');
    }

    public function test_failed_authentication_raises_an_igdb_exception(): void
    {
        Http::fake(['id.twitch.tv/*' => Http::response(['message' => 'invalid client secret'], 403)]);

        $this->expectException(IgdbException::class);

        app(IgdbService::class)->search('witcher');
    }
}
