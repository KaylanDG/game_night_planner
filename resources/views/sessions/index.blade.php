<x-layouts.app title="Sessions">
    <div class="flex flex-wrap items-end justify-between gap-5">
        <div class="flex flex-col gap-3.5">
            <p class="label text-cyan">&gt; Stage select</p>
            <h1 class="m-0 font-pixel text-[28px] leading-[1.35]">Sessions</h1>
            <p class="m-0 text-[26px] text-muted">Plan the next game night and look back at the last ones.</p>
        </div>
        <a class="btn btn-yellow px-[22px] py-4 text-xs" href="{{ route('sessions.create') }}" data-dialog-open="new-session">
            <x-icon name="plus" />New session
        </a>
    </div>

    <section aria-labelledby="upcoming-title" class="mt-3 flex flex-col gap-6">
        <div class="flex items-center gap-4 border-b-3 border-dashed border-line pb-4">
            <h2 id="upcoming-title" class="label text-[13px] text-yellow">Upcoming</h2>
            <span class="border-2 border-black bg-yellow px-2 py-1.5 font-pixel text-[10px] text-ink">{{ $upcoming->count() }}</span>
        </div>

        @if ($upcoming->isEmpty())
            <p class="m-0 text-2xl text-muted">No upcoming sessions. Start one with “New session”.</p>
        @else
            <div class="grid grid-cols-[repeat(auto-fill,minmax(min(360px,100%),1fr))] gap-7">
                @foreach ($upcoming as $session)
                    <a class="group flex text-inherit no-underline hover:text-inherit" href="{{ route('sessions.show', $session) }}">
                        <div @class(['panel lift flex flex-1 flex-col gap-[18px] p-6', 'border-yellow' => $loop->first])>
                            <div class="flex items-start gap-5">
                                <x-date-badge :date="$session->starts_at" />
                                <div class="flex min-w-0 flex-1 flex-col gap-2">
                                    @if ($loop->first)
                                        <span class="badge bg-yellow">Next up</span>
                                    @endif
                                    <h3 class="m-0 font-pixel text-sm leading-normal text-text">{{ $session->title }}</h3>
                                    <p class="m-0 text-2xl text-muted">{{ $session->starts_at->format('H:i') }} · <span class="text-cyan">{{ $session->going_attendees_count }} going</span></p>
                                </div>
                            </div>
                            <div class="flex flex-col gap-2.5 border-t-3 border-track pt-4">
                                <p class="label text-[8px]">Suggested games · {{ $session->games->count() }}</p>
                                @forelse ($session->games as $game)
                                    <div class="flex items-center gap-3">
                                        <x-game-cover :name="$game->name" :image-id="$game->cover_image_id" :seed="$game->igdb_id" size="thumb" class="h-10 w-[30px] border-2 text-[6px]" />
                                        <span class="text-2xl text-text">{{ $game->name }}</span>
                                    </div>
                                @empty
                                    <p class="m-0 text-[22px] text-muted">No games suggested yet.</p>
                                @endforelse
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    @if ($awaitingCompletion->isNotEmpty())
        <section aria-labelledby="awaiting-title" class="mt-3 flex flex-col gap-6">
            <div class="flex items-center gap-4 border-b-3 border-dashed border-line pb-4">
                <h2 id="awaiting-title" class="label text-[13px] text-orange">Awaiting completion</h2>
                <span class="border-2 border-black bg-orange px-2 py-1.5 font-pixel text-[10px] text-ink">{{ $awaitingCompletion->count() }}</span>
            </div>
            <div class="grid grid-cols-[repeat(auto-fill,minmax(min(280px,100%),1fr))] gap-6">
                @foreach ($awaitingCompletion as $session)
                    <a class="group flex text-inherit no-underline hover:text-inherit" href="{{ route('sessions.show', $session) }}">
                        <div class="panel lift flex flex-1 flex-col gap-3.5 bg-panel-dim p-5">
                            <div class="flex items-center justify-between gap-2.5">
                                <span class="font-pixel text-[10px] text-muted uppercase">{{ $session->starts_at->format('D d M') }}</span>
                                <span class="badge bg-orange">To complete</span>
                            </div>
                            <h3 class="m-0 font-pixel text-xs leading-normal text-text">{{ $session->title }}</h3>
                            <p class="m-0 text-[22px] text-muted">{{ $session->starts_at->format('H:i') }} · {{ $session->going_attendees_count }} going · {{ trans_choice(':count game|:count games', $session->games_count) }}</p>
                            <p class="m-0 text-[22px] text-muted">Created by {{ $session->creator->name }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section aria-labelledby="past-title" class="mt-3 flex flex-col gap-6">
        <div class="flex items-center gap-4 border-b-3 border-dashed border-line pb-4">
            <h2 id="past-title" class="label text-[13px] text-text">Completed</h2>
            <span class="border-2 border-black bg-track px-2 py-1.5 font-pixel text-[10px] text-text">{{ $completed->count() }}</span>
        </div>

        @if ($completed->isEmpty())
            <p class="m-0 text-2xl text-muted">No completed sessions yet.</p>
        @else
            <div class="grid grid-cols-[repeat(auto-fill,minmax(min(280px,100%),1fr))] gap-6">
                @foreach ($completed as $session)
                    <a class="group flex text-inherit no-underline hover:text-inherit" href="{{ route('sessions.show', $session) }}">
                        <div class="panel lift flex flex-1 flex-col gap-3.5 bg-panel-dim p-5">
                            <div class="flex items-center justify-between gap-2.5">
                                <span class="font-pixel text-[10px] text-muted uppercase">{{ $session->starts_at->format('D d M Y') }}</span>
                                <span class="badge bg-green">Cleared</span>
                            </div>
                            <h3 class="m-0 font-pixel text-xs leading-normal text-text">{{ $session->title }}</h3>
                            <p class="m-0 text-[22px] text-muted">{{ $session->starts_at->format('H:i') }} · {{ $session->goingAttendees->count() }} attended</p>
                            <p class="m-0 text-[22px] text-soft">Attendees: {{ $session->goingAttendees->pluck('name')->join(', ') ?: 'None' }}</p>
                            <div class="flex flex-col gap-1.5 border-t-3 border-track pt-3">
                                <p class="label text-[8px]">Played · {{ $session->playedGames->count() }} of {{ $session->games_count }}</p>
                                @forelse ($session->playedGames as $game)
                                    <div class="flex items-center gap-2.5">
                                        <x-game-cover :name="$game->name" :image-id="$game->cover_image_id" :seed="$game->igdb_id" size="thumb" class="h-[30px] w-[22px] border-2 text-[0px]" />
                                        <span class="text-[22px] text-soft">{{ $game->name }}</span>
                                    </div>
                                @empty
                                    <p class="m-0 text-[22px] text-muted">No games recorded</p>
                                @endforelse
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <x-modal id="new-session" title="New session" :open="$errors->any()">
        <x-slot:description>Pick a date and time. That’s it.</x-slot:description>

        <form method="POST" action="{{ route('sessions.store') }}" class="m-0 flex flex-col gap-[22px]">
            @include('sessions.partials.form', ['session' => null, 'idPrefix' => 'new-'])

            <div class="flex flex-wrap justify-end gap-3.5">
                <button class="btn btn-ghost" type="button" data-dialog-close>Cancel</button>
                <button class="btn btn-yellow" type="submit">Create session</button>
            </div>
        </form>
    </x-modal>
</x-layouts.app>
