@php
    $user = auth()->user();
    $groups = $nextSession?->attendanceGroups($members);
    $topCount = $mostPlayedGames->max('played_sessions_count') ?: 1;
    $rankColors = ['text-yellow', 'text-soft', 'text-orange'];
@endphp

<x-layouts.app title="Home">
    <div class="flex flex-wrap items-end justify-between gap-5">
        <div class="flex flex-col gap-3.5">
            <p class="label text-cyan">&gt; Player 1 ready<span class="blink">_</span></p>
            <h1 class="m-0 font-pixel text-[28px] leading-[1.35] break-words">Welcome back, {{ $user->name }}</h1>
            <p class="m-0 text-[26px] text-muted">
                @if ($nextSession)
                    Your party of {{ $members->count() }} has a game night coming up in {{ $nextSession->starts_at->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE) }}.
                @else
                    Your party of {{ $members->count() }} has no game night planned yet.
                @endif
            </p>
        </div>
        <a class="btn btn-yellow" href="{{ route('sessions.index') }}#new-session"><x-icon name="plus" />New session</a>
    </div>

    <div class="flex flex-wrap gap-6">
        <section class="panel flex min-w-0 flex-[2_1_min(560px,100%)] flex-col gap-6 border-yellow p-7" aria-labelledby="next-title">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="next-title" class="label text-yellow">Next session</h2>
                @if ($nextSession)
                    <span class="border-3 border-black bg-yellow px-2.5 py-2 font-pixel text-[10px] text-ink">In {{ $nextSession->starts_at->diffForHumans(syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE) }}</span>
                @endif
            </div>

            @if ($nextSession)
                <div class="flex flex-wrap items-center gap-7">
                    <x-date-badge :date="$nextSession->starts_at" size="lg" />
                    <div class="flex min-w-0 flex-[1_1_240px] flex-col gap-3">
                        <p class="m-0 font-pixel text-xl leading-[1.4]">{{ $nextSession->title }}</p>
                        <p class="m-0 text-[26px] text-muted">{{ $nextSession->starts_at->format('l j F') }} · {{ $nextSession->starts_at->format('H:i') }} · created by {{ $nextSession->creator->name }}</p>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <p class="label">Attendance</p>
                        <p class="m-0 text-2xl">
                            <span class="text-cyan">{{ $nextSession->going_attendees_count }} going</span> ·
                            <span class="text-orange">{{ $groups['not_going']->count() }} can't</span> ·
                            <span class="text-muted">{{ $groups['unconfirmed']->count() }} unconfirmed</span>
                        </p>
                    </div>
                    <x-attendance-bar :going="$groups['going']->count()" :not-going="$groups['not_going']->count()" :unconfirmed="$groups['unconfirmed']->count()" />
                </div>

                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach ($groups['going'] as $member)
                            <x-avatar :user="$member" :size="40" />
                        @endforeach
                        <span class="ml-1.5 text-[22px] text-muted">+ {{ trans_choice(':count suggested game|:count suggested games', $nextSession->games_count) }}</span>
                    </div>
                    <a class="btn btn-cyan" href="{{ route('sessions.show', $nextSession) }}">Open session<x-icon name="arrow-right" /></a>
                </div>
            @else
                <div class="flex flex-col items-center gap-4 border-3 border-dashed border-line px-5 py-10 text-center">
                    <p class="m-0 font-pixel text-xs text-pink">No upcoming sessions</p>
                    <p class="m-0 text-2xl text-muted">Plan the next game night for your party.</p>
                    <a class="btn btn-yellow" href="{{ route('sessions.create') }}"><x-icon name="plus" />Plan one</a>
                </div>
            @endif
        </section>

        <div class="flex min-w-0 flex-[1_1_280px] flex-col gap-6">
            <section class="panel flex flex-1 flex-col justify-between gap-[18px] p-6">
                <h2 class="label">Total sessions</h2>
                <p class="m-0 font-pixel text-5xl text-cyan text-shadow-[4px_4px_0_#000]">{{ $totalSessions }}</p>
                <p class="m-0 text-2xl text-muted">completed game nights</p>
            </section>
            <section class="panel flex flex-1 flex-col justify-between gap-[18px] p-6">
                <h2 class="label">Games played</h2>
                <p class="m-0 font-pixel text-5xl text-yellow text-shadow-[4px_4px_0_#000]">{{ $gamesPlayed }}</p>
                <p class="m-0 text-2xl text-muted">unique games played together</p>
            </section>
        </div>
    </div>

    <div class="flex flex-wrap gap-6">
        <section class="panel flex min-w-0 flex-[2_1_min(560px,100%)] flex-col gap-5 p-7" aria-labelledby="top-title">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 id="top-title" class="label text-text">Most played · High scores</h2>
                <p class="m-0 text-[22px] text-muted">from completed sessions</p>
            </div>

            @if ($mostPlayedGames->isEmpty())
                <div class="flex flex-col items-center gap-3 border-3 border-dashed border-line px-5 py-10 text-center">
                    <p class="m-0 font-pixel text-xs text-pink">No high scores yet</p>
                    <p class="m-0 text-2xl text-muted">No games played yet. Complete a session to start the leaderboard.</p>
                </div>
            @else
                <ol class="m-0 flex list-none flex-col gap-3.5 p-0">
                    @foreach ($mostPlayedGames as $game)
                        <li>
                            <a class="group block text-inherit no-underline hover:text-inherit" href="{{ route('games.show', $game->igdb_id) }}">
                                <div class="lift flex items-center gap-5 border-3 border-line bg-night px-[18px] py-3.5">
                                    <span class="{{ $rankColors[$loop->index] }} w-[52px] flex-none font-pixel text-sm">{{ ['1ST', '2ND', '3RD'][$loop->index] }}</span>
                                    <x-game-cover :name="$game->name" :image-id="$game->cover_image_id" :seed="$game->igdb_id" size="cover_small" class="h-[72px] w-[54px] text-[8px]" />
                                    <div class="flex min-w-0 flex-1 flex-col gap-2.5">
                                        <p class="m-0 text-[28px] leading-none text-text">{{ $game->name }}</p>
                                        <div class="h-3.5 border-2 border-black bg-track">
                                            <div class="h-full bg-cyan" style="width: {{ round($game->played_sessions_count / $topCount * 100) }}%"></div>
                                        </div>
                                    </div>
                                    <div class="flex flex-none flex-col gap-1.5 text-right">
                                        <span class="font-pixel text-base text-text">{{ $game->played_sessions_count }}</span>
                                        <span class="label text-[8px]">{{ $game->played_sessions_count === 1 ? 'night' : 'nights' }}</span>
                                        <span class="sr-only">played in {{ trans_choice(':count session|:count sessions', $game->played_sessions_count) }}</span>
                                    </div>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        <section class="panel flex min-w-0 flex-[1_1_280px] flex-col gap-5 p-7" aria-labelledby="members-title">
            <div class="flex items-center justify-between gap-2">
                <h2 id="members-title" class="label text-text">Party members</h2>
                <span class="font-pixel text-[10px] text-cyan">{{ $members->count() }}</span>
            </div>
            <ul class="m-0 flex list-none flex-col gap-2.5 p-0">
                @foreach ($members as $member)
                    <li class="flex items-center gap-3.5">
                        <x-avatar :user="$member" :size="36" aria-hidden="true" />
                        <span class="flex-1 text-[26px]">{{ $member->name }}</span>
                        @if ($member->is($user))
                            <span class="bg-cyan px-2 py-1.5 font-pixel text-[8px] text-ink uppercase">You</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-layouts.app>
