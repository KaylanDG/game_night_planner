@php
    use App\Enums\AttendanceStatus;

    $me = auth()->user();
    $myStatus = $session->attendanceStatusFor($me);
    $groups = $session->attendanceGroups($users);
    $playedCount = $session->suggestions->where('was_played', true)->count();

    [$statusLabel, $statusBorder, $statusBg] = match (true) {
        $session->isCompleted() => ['Cleared', 'border-green', 'bg-green'],
        $session->hasStarted() => ['Awaiting completion', 'border-orange', 'bg-orange'],
        default => ['Upcoming', 'border-yellow', 'bg-yellow'],
    };
@endphp

<x-layouts.app :title="$session->title">
    <a href="{{ route('sessions.index') }}" class="-mt-2 inline-flex min-h-11 items-center gap-2.5 self-start font-pixel text-[10px] uppercase no-underline">
        <x-icon name="arrow-left" />All sessions
    </a>

    @if (session('status') === 'session-completed')
        <div role="status" class="flex flex-wrap items-center gap-x-6 gap-y-4 border-3 border-black bg-green px-[22px] py-[18px] text-ink shadow-[6px_6px_0_#000]">
            <p class="m-0 font-pixel text-base">GG!</p>
            <p class="m-0 flex-[1_1_300px] text-[26px]">
                Session complete —
                {{ $playedCount === 0 ? 'no games were marked as played.' : "{$playedCount} of {$session->games->count()} suggested games played." }}
                Added to your history and Most played.
            </p>
        </div>
    @endif

    <section class="panel {{ $statusBorder }} flex flex-wrap items-center gap-7 p-7" aria-labelledby="session-title">
        <x-date-badge :date="$session->starts_at" class="w-[104px]" size="lg" />

        <div class="flex min-w-0 flex-[1_1_min(360px,100%)] flex-col gap-3">
            <span class="badge {{ $statusBg }} text-[9px]">{{ $statusLabel }}</span>
            <h1 id="session-title" class="m-0 font-pixel text-[26px] leading-[1.4] text-shadow-[4px_4px_0_#000]">{{ $session->title }}</h1>
            <p class="m-0 text-[26px] text-soft">
                {{ $session->starts_at->format('l j F Y') }} · {{ $session->starts_at->format('H:i') }} ·
                <span class="text-muted">created by</span> {{ $session->creator->name }}
            </p>
        </div>

        @if (auth()->user()->can('update', $session) || auth()->user()->can('complete', $session))
            <div class="flex flex-wrap gap-3.5">
                @can('update', $session)
                    <a class="btn btn-ghost" href="{{ route('sessions.edit', $session) }}">Edit session</a>
                @endcan
                @can('complete', $session)
                    <a class="btn btn-yellow" href="{{ route('sessions.complete.create', $session) }}" data-dialog-open="complete-session">Complete session</a>
                @endcan
            </div>
        @endif
    </section>

    <div class="flex flex-wrap items-start gap-7">
        <section class="panel flex min-w-0 flex-[1_1_min(360px,100%)] flex-col gap-[22px] p-7" aria-labelledby="attendance-title">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="attendance-title" class="label text-text">Attendance</h2>
                <span class="text-[22px] text-muted"><span class="text-cyan">{{ $groups['going']->count() }}</span> of {{ $users->count() }} going</span>
            </div>

            @unless ($session->isCompleted())
                <div class="flex flex-col gap-3 border-3 border-dashed border-line bg-night p-[18px]">
                    <p class="label text-[8px] text-yellow">Are you in, {{ $me->name }}?</p>
                    <form method="POST" action="{{ route('sessions.attendance.update', $session) }}" class="m-0 flex flex-wrap gap-3">
                        @csrf
                        @method('PUT')
                        @foreach ([AttendanceStatus::Going, AttendanceStatus::NotGoing] as $option)
                            <button
                                type="submit"
                                name="status"
                                value="{{ $option->value }}"
                                aria-pressed="{{ $myStatus === $option ? 'true' : 'false' }}"
                                @cannot('respond', $session) disabled @endcannot
                                @class([
                                    'btn flex-[1_1_140px] px-3 py-4',
                                    'btn-cyan' => $myStatus === $option && $option === AttendanceStatus::Going,
                                    'btn-orange' => $myStatus === $option && $option === AttendanceStatus::NotGoing,
                                    'btn-dark' => $myStatus !== $option,
                                ])
                            >{{ $option === AttendanceStatus::Going ? 'Going' : 'Can’t make it' }}</button>
                        @endforeach
                    </form>
                    @cannot('respond', $session)
                        <p class="m-0 text-[22px] text-muted">This session has started; responses are closed.</p>
                    @endcannot
                    @error('status') <p class="field-error"><x-icon name="alert" />{{ $message }}</p> @enderror
                </div>
            @endunless

            <x-attendance-bar :going="$groups['going']->count()" :not-going="$groups['not_going']->count()" :unconfirmed="$groups['unconfirmed']->count()" height="h-[18px]" />

            @foreach ([
                ['Going', 'text-cyan', $groups['going']],
                [AttendanceStatus::NotGoing->label(), 'text-orange', $groups['not_going']],
                ['Unconfirmed', 'text-muted', $groups['unconfirmed']],
            ] as [$title, $color, $people])
                <div class="flex flex-col gap-2.5">
                    <h3 class="label {{ $color }} text-[8px]">{{ $title }} · {{ $people->count() }}</h3>
                    @if ($people->isEmpty())
                        <p class="m-0 text-[22px] text-muted">Nobody yet.</p>
                    @else
                        <ul class="m-0 flex list-none flex-col gap-2 p-0">
                            @foreach ($people as $person)
                                <li class="flex items-center gap-3">
                                    <x-avatar :user="$person" :size="34" aria-hidden="true" />
                                    <span class="text-2xl">{{ $person->name }}{{ $person->is($me) ? ' (you)' : '' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </section>

        <section class="panel flex min-w-0 flex-[2_1_min(520px,100%)] flex-col gap-5 p-7" aria-labelledby="games-title">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 id="games-title" class="label text-text">{{ $session->isCompleted() ? 'Games' : 'Suggested games' }} · {{ $session->games->count() }}</h2>
                <div class="flex flex-wrap gap-3">
                    @can('spin', $session)
                        <button class="btn btn-yellow" type="button" data-dialog-open="roulette">Spin the wheel</button>
                    @endcan
                    @can('suggestGame', $session)
                        <a class="btn btn-ghost" href="{{ route('games.index') }}"><x-icon name="plus" />Suggest a game</a>
                    @endcan
                </div>
            </div>

            @if ($session->games->isEmpty())
                <div class="flex flex-col items-center gap-3 border-3 border-dashed border-line px-5 py-10 text-center">
                    <p class="m-0 font-pixel text-xs text-pink">No games yet</p>
                    <p class="m-0 text-2xl text-muted">
                        {{ $session->isCompleted() ? 'No games were suggested for this session.' : 'No games suggested yet. Browse the catalogue and suggest something to play.' }}
                    </p>
                </div>
            @else
                @if ($session->isCompleted())
                    <p class="m-0 text-2xl text-muted">{{ $playedCount }} of {{ $session->games->count() }} played</p>
                @endif

                {{-- Played games first so the session's history reads at a glance. --}}
                <ul class="m-0 flex list-none flex-col gap-3.5 p-0">
                    @foreach ($session->suggestions->sortByDesc('was_played') as $suggestion)
                        @php
                            $game = $suggestion->game;
                            $myVote = $suggestion->voteBy($me);
                            $canVote = $me->can('vote', $suggestion);
                        @endphp
                        <li id="suggestion-{{ $suggestion->id }}" @class(['flex flex-wrap items-center gap-4 border-3 border-line bg-night p-3.5', 'opacity-60' => $session->isCompleted() && ! $suggestion->was_played])>
                            <a class="group flex min-h-11 min-w-0 flex-[1_1_240px] items-center gap-[18px] text-inherit no-underline hover:text-inherit" href="{{ route('games.show', $game->igdb_id) }}">
                                <x-game-cover :name="$game->name" :image-id="$game->cover_image_id" :seed="$game->igdb_id" size="cover_small" class="h-20 w-[60px] text-[9px]" />
                                <div class="flex min-w-0 flex-col gap-1.5">
                                    <span class="text-[28px] leading-none text-text group-hover:text-yellow">{{ $game->name }}</span>
                                    <span class="text-[21px] text-muted">
                                        {{ $game->release_date?->year ?? 'Year unknown' }}
                                        @if ($suggestion->suggester)
                                            · suggested by {{ $suggestion->suggester->is($me) ? 'you' : $suggestion->suggester->name }}
                                        @endif
                                    </span>
                                </div>
                            </a>

                            <div class="flex flex-none items-center gap-2.5">
                                <form method="POST" action="{{ route('suggestions.vote', $suggestion) }}" class="m-0 flex gap-2">
                                    @csrf
                                    @method('PUT')
                                    @foreach ([\App\Enums\Vote::Like, \App\Enums\Vote::Dislike] as $vote)
                                        @php($isLike = $vote === \App\Enums\Vote::Like)
                                        <button
                                            type="submit"
                                            name="vote"
                                            value="{{ $vote->value }}"
                                            aria-pressed="{{ $myVote === $vote ? 'true' : 'false' }}"
                                            aria-label="{{ $isLike ? 'Like' : 'Dislike' }} {{ $game->name }} ({{ $suggestion->voteCount($vote) }})"
                                            title="{{ $canVote ? ($myVote === $vote ? 'Remove your vote' : ($isLike ? 'Like' : 'Dislike')) : 'Voting is closed' }}"
                                            @disabled(! $canVote)
                                            @class([
                                                'btn min-w-[68px] gap-2 px-3 py-2.5 text-[10px] disabled:opacity-70',
                                                'btn-cyan' => $myVote === $vote && $isLike,
                                                'btn-orange' => $myVote === $vote && ! $isLike,
                                                'btn-dark' => $myVote !== $vote,
                                            ])
                                        ><x-icon :name="$isLike ? 'thumb-up' : 'thumb-down'" :size="14" />{{ $suggestion->voteCount($vote) }}</button>
                                    @endforeach
                                </form>

                                @if ($session->isCompleted())
                                    <span @class(['badge flex-none self-center', 'bg-green text-ink' => $suggestion->was_played, 'bg-track text-muted' => ! $suggestion->was_played])>{{ $suggestion->was_played ? 'Played' : 'Not played' }}</span>
                                @endif

                                @can('delete', $suggestion)
                                    <form method="POST" action="{{ route('suggestions.destroy', $suggestion) }}" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" aria-label="Remove {{ $game->name }}" title="Remove suggestion" class="flex size-11 flex-none cursor-pointer items-center justify-center border-3 border-edge bg-night text-text hover:border-orange hover:text-orange">
                                            <x-icon name="close" :size="14" />
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    @can('spin', $session)
        <x-modal id="roulette" title="Game roulette">
            <x-slot:description>Every suggested game has an equal shot, whatever the votes say.</x-slot:description>
            @include('sessions.partials.roulette')
        </x-modal>
    @endcan

    @can('complete', $session)
        <x-modal id="complete-session" title="Complete session" accent="green" :open="$errors->has('played_games') || $errors->has('played_games.*')">
            <x-slot:description>Which of the suggested games did you actually play? Only checked games count toward history and Most played.</x-slot:description>
            @include('sessions.partials.complete-form')
        </x-modal>
    @endcan
</x-layouts.app>
