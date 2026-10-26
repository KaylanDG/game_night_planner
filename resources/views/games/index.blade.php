<x-layouts.app title="Games">
    <div class="flex flex-col gap-3.5">
        <p class="label text-cyan">&gt; Select your game</p>
        <h1 class="m-0 font-pixel text-[28px] leading-[1.35]">Game catalogue</h1>
        <p class="m-0 text-[26px] text-muted">Find a game, open it, and suggest it for an upcoming session.</p>
    </div>

    <form method="GET" action="{{ route('games.index') }}" role="search" class="m-0 flex max-w-[820px] flex-col gap-3">
        <label for="search" class="label text-text">Search games</label>
        <div class="relative">
            <x-icon name="search" :size="28" class="pointer-events-none absolute top-5 left-5 text-cyan" />
            <input
                id="search"
                type="search"
                name="search"
                value="{{ $search }}"
                maxlength="100"
                placeholder="Type a game name…"
                autocomplete="off"
                class="field min-h-[68px] py-3 pr-4 pl-16 text-[30px] shadow-[6px_6px_0_#000] focus:shadow-[6px_6px_0_#000]"
            >
        </div>
        @error('search') <p class="field-error"><x-icon name="alert" />{{ $message }}</p> @enderror
        <p class="m-0 text-xl text-muted">Game data from IGDB · press Enter to search</p>
    </form>

    <section aria-live="polite" aria-labelledby="results-title" class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b-3 border-dashed border-line pb-4">
            <h2 id="results-title" class="label text-xs text-yellow">
                {{ $search ? "Results for \"{$search}\"" : 'Popular right now' }}{{ $page > 1 ? " · page {$page}" : '' }}
            </h2>
            @if ($search)
                <a class="btn btn-ghost" href="{{ route('games.index') }}">Clear search</a>
            @endif
        </div>

        @if ($error)
            <div class="panel flex flex-col items-center gap-[18px] px-6 py-14 text-center">
                <p class="m-0 font-pixel text-[22px] text-orange text-shadow-[4px_4px_0_#000]">Connection lost</p>
                <p class="m-0 text-[26px] text-muted">{{ $error }} Please try again later.</p>
            </div>
        @elseif (empty($games))
            <div class="panel flex flex-col items-center gap-[18px] px-6 py-14 text-center">
                <p class="m-0 font-pixel text-[22px] text-pink text-shadow-[4px_4px_0_#000]">Game over</p>
                <p class="m-0 text-[26px] text-muted">No games found{{ $search ? " for “{$search}”" : '' }}. Try another name.</p>
            </div>
        @else
            <ul class="m-0 grid list-none grid-cols-[repeat(auto-fill,minmax(min(176px,100%),1fr))] gap-x-7 gap-y-9 p-0">
                @foreach ($games as $game)
                    <li>
                        <a class="group flex flex-col gap-3 text-inherit no-underline hover:text-inherit" href="{{ route('games.show', $game['igdb_id']) }}">
                            <x-game-cover
                                :name="$game['name']"
                                :image-id="$game['cover_image_id']"
                                :seed="$game['igdb_id']"
                                class="aspect-[3/4] w-full p-3.5 text-[11px] shadow-[4px_4px_0_#000] transition-[transform,box-shadow] duration-75 group-hover:-translate-x-[3px] group-hover:-translate-y-[3px] group-hover:shadow-[7px_7px_0_#000] group-hover:outline-3 group-hover:outline-cyan motion-reduce:transition-none"
                            >{{ $game['name'] }}</x-game-cover>
                            <div class="flex flex-col gap-1">
                                <h3 class="m-0 text-[26px] leading-none font-normal text-text group-hover:text-yellow">{{ $game['name'] }}</h3>
                                <span class="font-pixel text-[9px] text-muted">{{ $game['release_year'] ?? 'Release year unknown' }}</span>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($page > 1 || $hasMore)
                <nav aria-label="Pagination" class="flex flex-wrap justify-center gap-4">
                    @if ($page > 1)
                        <a class="btn btn-ghost" href="{{ route('games.index', array_filter(['search' => $search, 'page' => $page - 1])) }}"><x-icon name="arrow-left" />Previous</a>
                    @endif
                    @if ($hasMore)
                        <a class="btn btn-ghost" href="{{ route('games.index', array_filter(['search' => $search, 'page' => $page + 1])) }}">Next<x-icon name="arrow-right" /></a>
                    @endif
                </nav>
            @endif
        @endif
    </section>
</x-layouts.app>
