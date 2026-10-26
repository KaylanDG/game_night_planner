<x-layouts.app :title="$game['name'] ?? 'Game'">
    <a href="{{ route('games.index') }}" class="-mt-2 inline-flex min-h-11 items-center gap-2.5 self-start font-pixel text-[10px] uppercase no-underline">
        <x-icon name="arrow-left" />Back to games
    </a>

    @if ($error)
        <div class="panel flex flex-col items-center gap-[18px] px-6 py-14 text-center">
            <h1 class="m-0 font-pixel text-[22px] text-orange text-shadow-[4px_4px_0_#000]">Game unavailable</h1>
            <p class="m-0 text-[26px] text-muted">{{ $error }} Please try again later.</p>
        </div>
    @else
        @php
            $suggestedSessions = $sessions->whereIn('id', $suggestedSessionIds);
            $openSessions = $sessions->whereNotIn('id', $suggestedSessionIds);
        @endphp

        @if ($suggested = session('suggestedTo'))
            <div role="status" class="flex flex-wrap items-center justify-between gap-4 border-3 border-black bg-cyan px-5 py-4 text-ink shadow-[6px_6px_0_#000]">
                <p class="m-0 font-pixel text-[11px] leading-[1.6]">+1 suggestion · Added to {{ $suggested['title'] }}</p>
                <a href="{{ route('sessions.show', $suggested['id']) }}" class="inline-flex min-h-11 items-center font-pixel text-[10px] text-ink hover:text-ink">View session</a>
            </div>
        @endif

        <div class="flex flex-wrap items-start gap-12">
            <x-game-cover
                :name="$game['name']"
                :image-id="$game['cover_image_id']"
                :seed="$game['igdb_id']"
                size="cover_big"
                label
                class="aspect-[3/4] w-full max-w-[320px] flex-[0_1_min(320px,100%)] p-6 text-xl shadow-[8px_8px_0_#000]"
            >{{ $game['name'] }}</x-game-cover>

            <div class="flex min-w-0 flex-[1_1_min(480px,100%)] flex-col gap-6">
                <div class="flex flex-col gap-4">
                    <p class="label text-cyan">
                        @if ($game['release_date'])
                            Released {{ $game['release_date']->format('j F Y') }}
                        @else
                            Release date unknown
                        @endif
                    </p>
                    <h1 class="m-0 font-pixel text-[32px] leading-[1.35] text-shadow-[4px_4px_0_#000]">{{ $game['name'] }}</h1>
                </div>

                @if ($game['genres'])
                    <div class="flex flex-col gap-2.5">
                        <h2 class="label">Genres</h2>
                        <ul class="m-0 flex list-none flex-wrap gap-2.5 p-0">
                            @foreach ($game['genres'] as $genre)
                                <li class="chip">{{ $genre }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($game['platforms'])
                    <div class="flex flex-col gap-2.5">
                        <h2 class="label">Platforms</h2>
                        <ul class="m-0 flex list-none flex-wrap gap-2.5 p-0">
                            @foreach ($game['platforms'] as $platform)
                                <li class="chip border-line bg-night text-soft">{{ $platform }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($game['summary'])
                    <div class="flex flex-col gap-2.5">
                        <h2 class="label">About</h2>
                        <p class="m-0 max-w-[640px] text-[26px] leading-[1.35] text-soft">{{ $game['summary'] }}</p>
                    </div>
                @endif

                <div class="flex flex-wrap items-center gap-4 pt-2">
                    @if ($sessions->isEmpty())
                        <a class="btn btn-yellow px-6 py-[18px] text-[13px]" href="{{ route('sessions.create') }}"><x-icon name="plus" :size="14" />Plan a session</a>
                        <span class="text-[22px] text-muted">There are no upcoming sessions to add this game to.</span>
                    @else
                        <button class="btn btn-yellow px-6 py-[18px] text-[13px]" type="button" data-dialog-open="add-to-session"><x-icon name="plus" :size="14" />Add to session</button>
                        @if ($suggestedSessions->isNotEmpty())
                            <span class="text-[22px] text-muted">Already suggested for {{ $suggestedSessions->pluck('title')->join(', ', ' and ') }}</span>
                        @endif
                    @endif
                </div>

                @error('session_id')
                    <p class="field-error"><x-icon name="alert" />{{ $message }}</p>
                @enderror
            </div>
        </div>

        @if ($sessions->isNotEmpty())
            <x-modal id="add-to-session" title="Add to session" :open="$errors->has('session_id')">
                <x-slot:description>Suggest {{ $game['name'] }} for an upcoming game night.</x-slot:description>

                <form method="POST" action="{{ route('games.suggestions.store', $game['igdb_id']) }}" class="m-0 flex flex-col gap-[22px]">
                    @csrf

                    <fieldset class="m-0 flex flex-col gap-2.5 border-0 p-0">
                        <legend class="label mb-3">Upcoming sessions</legend>

                        @foreach ($sessions as $session)
                            @php($alreadySuggested = in_array($session->id, $suggestedSessionIds))
                            <label class="choice [&:has(input:checked)]:border-yellow [&:has(input:checked)]:bg-[#26220f]">
                                <input
                                    type="radio"
                                    name="session_id"
                                    value="{{ $session->id }}"
                                    class="accent-yellow"
                                    required
                                    @checked(old('session_id', $openSessions->first()?->id) == $session->id)
                                    @disabled($alreadySuggested)
                                >
                                <x-date-badge :date="$session->starts_at" size="sm" />
                                <div class="flex min-w-0 flex-1 flex-col gap-1">
                                    <span class="text-[26px] leading-none text-text">{{ $session->title }}</span>
                                    <span class="text-xl text-muted">
                                        {{ $session->starts_at->format('D j M · H:i') }} ·
                                        {{ $alreadySuggested ? 'Already suggested' : "{$session->going_attendees_count} going" }}
                                    </span>
                                </div>
                            </label>
                        @endforeach

                        @error('session_id') <p class="field-error"><x-icon name="alert" />{{ $message }}</p> @enderror
                    </fieldset>

                    <div class="flex flex-wrap justify-end gap-3.5">
                        <button class="btn btn-ghost" type="button" data-dialog-close>Cancel</button>
                        <button class="btn btn-yellow" type="submit" @disabled($openSessions->isEmpty())>Suggest game</button>
                    </div>
                </form>
            </x-modal>
        @endif
    @endif
</x-layouts.app>
