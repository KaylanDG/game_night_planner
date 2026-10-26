<x-layouts.app title="New session">
    <a href="{{ route('sessions.index') }}" class="-mt-2 inline-flex min-h-11 items-center gap-2.5 self-start font-pixel text-[10px] uppercase no-underline">
        <x-icon name="arrow-left" />All sessions
    </a>

    <section class="panel flex w-full max-w-[560px] flex-col gap-[22px] border-yellow p-7" aria-labelledby="create-title">
        <div class="flex flex-col gap-2.5">
            <h1 id="create-title" class="m-0 font-pixel text-base leading-[1.4] text-yellow">New session</h1>
            <p class="m-0 text-2xl text-muted">Pick a date and time. That’s it.</p>
        </div>

        <form method="POST" action="{{ route('sessions.store') }}" class="m-0 flex flex-col gap-[22px]">
            @include('sessions.partials.form', ['session' => null])

            <div class="flex flex-wrap justify-end gap-3.5">
                <a class="btn btn-ghost" href="{{ route('sessions.index') }}">Cancel</a>
                <button class="btn btn-yellow" type="submit">Create session</button>
            </div>
        </form>
    </section>
</x-layouts.app>
