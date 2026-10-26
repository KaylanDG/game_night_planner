<form method="POST" action="{{ route('sessions.complete.store', $session) }}" class="m-0 flex flex-col gap-[22px]">
    @csrf

    <fieldset class="m-0 flex flex-col gap-2.5 border-0 p-0">
        <legend class="label mb-3">Played · {{ trans_choice(':count suggested game|:count suggested games', $session->games->count()) }}</legend>

        @forelse ($session->games as $game)
            <label class="choice">
                <input type="checkbox" name="played_games[]" value="{{ $game->id }}" @checked(in_array($game->id, old('played_games', [])))>
                <x-game-cover :name="$game->name" :image-id="$game->cover_image_id" :seed="$game->igdb_id" size="thumb" class="h-12 w-9 border-2 text-[6px]" />
                <span class="text-[26px] text-text">{{ $game->name }}</span>
            </label>
        @empty
            <p class="m-0 text-2xl text-muted">No games were suggested for this session.</p>
        @endforelse

        @error('played_games') <p class="field-error"><x-icon name="alert" />{{ $message }}</p> @enderror
        @error('played_games.*') <p class="field-error"><x-icon name="alert" />{{ $message }}</p> @enderror
    </fieldset>

    <p class="m-0 text-[22px] text-muted">Completed sessions can’t be edited afterwards.</p>

    <div class="flex flex-wrap justify-end gap-3.5">
        @if ($inDialog ?? true)
            <button class="btn btn-ghost" type="button" data-dialog-close>Cancel</button>
        @else
            <a class="btn btn-ghost" href="{{ route('sessions.show', $session) }}">Cancel</a>
        @endif
        <button class="btn btn-green" type="submit">Save &amp; complete</button>
    </div>
</form>
