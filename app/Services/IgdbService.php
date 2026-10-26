<?php

namespace App\Services;

use App\Exceptions\IgdbException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IgdbService
{
    public const PER_PAGE = 24;

    private const TOKEN_URL = 'https://id.twitch.tv/oauth2/token';

    private const API_URL = 'https://api.igdb.com/v4';

    private const IMAGE_URL = 'https://images.igdb.com/igdb/image/upload';

    private const TOKEN_CACHE_KEY = 'igdb.access_token';

    private const LIST_FIELDS = 'name, cover.image_id, first_release_date';

    private const DETAIL_FIELDS = 'name, cover.image_id, first_release_date, summary, genres.name, platforms.name';

    public function __construct(
        private readonly ?string $clientId,
        private readonly ?string $secret,
    ) {}

    /**
     * Search games by name, or list popular games when no query is given.
     *
     * @return array{games: list<array<string, mixed>>, has_more: bool}
     */
    public function search(?string $query, int $page = 1): array
    {
        $query = trim((string) $query);
        $offset = ($page - 1) * self::PER_PAGE;

        // Fetch one extra result to know whether there's another page.
        $body = $query === ''
            ? 'fields '.self::LIST_FIELDS.'; where cover != null & total_rating_count > 100; '
                .'sort total_rating_count desc; limit '.(self::PER_PAGE + 1)."; offset {$offset};"
            : 'search "'.$this->escape($query).'"; fields '.self::LIST_FIELDS.'; '
                .'limit '.(self::PER_PAGE + 1)."; offset {$offset};";

        $results = Cache::remember(
            'igdb.search.'.md5($body),
            now()->addHour(),
            fn () => $this->query('games', $body),
        );

        return [
            'games' => array_map($this->normalize(...), array_slice($results, 0, self::PER_PAGE)),
            'has_more' => count($results) > self::PER_PAGE,
        ];
    }

    /**
     * Fetch a single game's details, or null when IGDB has no such game.
     *
     * @return array<string, mixed>|null
     */
    public function find(int $igdbId): ?array
    {
        $results = Cache::remember(
            "igdb.game.{$igdbId}",
            now()->addDay(),
            fn () => $this->query('games', 'fields '.self::DETAIL_FIELDS."; where id = {$igdbId}; limit 1;"),
        );

        return isset($results[0]) ? $this->normalize($results[0]) : null;
    }

    public static function coverUrl(?string $imageId, string $size = 'cover_big'): ?string
    {
        return $imageId ? self::IMAGE_URL."/t_{$size}/{$imageId}.jpg" : null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function query(string $endpoint, string $body): array
    {
        try {
            $response = $this->request($body)->post(self::API_URL."/{$endpoint}");

            // A revoked or expired token: drop it and retry once with a fresh one.
            if ($response->unauthorized()) {
                Cache::forget(self::TOKEN_CACHE_KEY);
                $response = $this->request($body)->post(self::API_URL."/{$endpoint}");
            }

            return $response->throw()->json() ?? [];
        } catch (ConnectionException|RequestException $e) {
            Log::warning('IGDB request failed', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);

            throw new IgdbException('The game catalogue is currently unavailable.', previous: $e);
        }
    }

    private function request(string $body): PendingRequest
    {
        return Http::withHeaders(['Client-ID' => $this->clientId])
            ->withToken($this->accessToken())
            ->withBody($body, 'text/plain')
            ->acceptJson()
            ->timeout(10);
    }

    private function accessToken(): string
    {
        if ($token = Cache::get(self::TOKEN_CACHE_KEY)) {
            return $token;
        }

        if (! $this->clientId || ! $this->secret) {
            throw new IgdbException('IGDB credentials are not configured.');
        }

        try {
            $response = Http::asForm()->timeout(10)->post(self::TOKEN_URL, [
                'client_id' => $this->clientId,
                'client_secret' => $this->secret,
                'grant_type' => 'client_credentials',
            ])->throw();
        } catch (ConnectionException|RequestException $e) {
            Log::warning('IGDB authentication failed', ['error' => $e->getMessage()]);

            throw new IgdbException('Could not authenticate with IGDB.', previous: $e);
        }

        $token = $response->json('access_token');

        // Expire the cached token a minute early so it's never used stale.
        Cache::put(self::TOKEN_CACHE_KEY, $token, now()->addSeconds(max(60, $response->json('expires_in', 3600) - 60)));

        return $token;
    }

    private function escape(string $value): string
    {
        return str_replace(['\\', '"'], '', $value);
    }

    /**
     * @param  array<string, mixed>  $game
     * @return array<string, mixed>
     */
    private function normalize(array $game): array
    {
        $releaseDate = isset($game['first_release_date'])
            ? Carbon::createFromTimestampUTC($game['first_release_date'])->startOfDay()
            : null;

        return [
            'igdb_id' => $game['id'],
            'name' => $game['name'] ?? 'Unknown game',
            'cover_image_id' => $game['cover']['image_id'] ?? null,
            'release_date' => $releaseDate,
            'release_year' => $releaseDate?->year,
            'summary' => $game['summary'] ?? null,
            'genres' => array_column($game['genres'] ?? [], 'name'),
            'platforms' => array_column($game['platforms'] ?? [], 'name'),
        ];
    }
}
