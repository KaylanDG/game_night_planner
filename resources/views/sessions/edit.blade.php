<x-layouts.app title="Edit session">
    <a href="{{ route('sessions.show', $session) }}" class="-mt-2 inline-flex min-h-11 items-center gap-2.5 self-start font-pixel text-[10px] uppercase no-underline">
        <x-icon name="arrow-left" />Back to session
    </a>

    <section class="panel flex w-full max-w-[560px] flex-col gap-[22px] border-yellow p-7" aria-labelledby="edit-title">
        <div class="flex flex-col gap-2.5">
            <h1 id="edit-title" class="m-0 font-pixel text-base leading-[1.4] text-yellow">Edit session</h1>
            <p class="m-0 text-2xl text-muted">Change the name, date or start time.</p>
        </div>

        <form method="POST" action="{{ route('sessions.update', $session) }}" class="m-0 flex flex-col gap-[22px]">
            @method('PUT')
            @include('sessions.partials.form')

            <div class="flex flex-wrap justify-end gap-3.5">
                <a class="btn btn-ghost" href="{{ route('sessions.show', $session) }}">Cancel</a>
                <button class="btn btn-yellow" type="submit">Save changes</button>
            </div>
        </form>
    </section>

    @can('delete', $session)
        <section class="panel flex w-full max-w-[560px] flex-wrap items-center justify-between gap-4 border-orange p-7" aria-labelledby="danger-title">
            <div class="flex flex-col gap-2">
                <h2 id="danger-title" class="label text-orange">Danger zone</h2>
                <p class="m-0 text-2xl text-muted">Deleting removes attendance and suggestions too.</p>
            </div>
            <button class="btn btn-orange" type="button" data-dialog-open="delete-session">Delete session</button>
        </section>

        <x-modal id="delete-session" title="Delete session?">
            <x-slot:description>“{{ $session->title }}” and everything attached to it will be gone for good.</x-slot:description>

            <form method="POST" action="{{ route('sessions.destroy', $session) }}" class="m-0 flex flex-wrap justify-end gap-3.5">
                @csrf
                @method('DELETE')
                <button class="btn btn-ghost" type="button" data-dialog-close>Keep it</button>
                <button class="btn btn-orange" type="submit">Delete session</button>
            </form>
        </x-modal>
    @endcan
</x-layouts.app>
