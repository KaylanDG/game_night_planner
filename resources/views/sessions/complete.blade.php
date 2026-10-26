<x-layouts.app title="Complete session">
    <a href="{{ route('sessions.show', $session) }}" class="-mt-2 inline-flex min-h-11 items-center gap-2.5 self-start font-pixel text-[10px] uppercase no-underline">
        <x-icon name="arrow-left" />Back to session
    </a>

    <section class="panel flex w-full max-w-[560px] flex-col gap-[22px] border-green p-7" aria-labelledby="complete-title">
        <div class="flex flex-col gap-2.5">
            <h1 id="complete-title" class="m-0 font-pixel text-base leading-[1.4] text-green">Complete “{{ $session->title }}”</h1>
            <p class="m-0 text-2xl text-muted">{{ $session->starts_at->format('l j F Y, H:i') }}. Which of the suggested games did you actually play? Only checked games count toward history and Most played.</p>
        </div>

        @include('sessions.partials.complete-form', ['inDialog' => false])
    </section>
</x-layouts.app>
