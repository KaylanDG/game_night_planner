<x-layouts.guest>
    <x-auth-hero
        heading="GAME<br>NIGHT"
        tagline="Plan game nights, pick what to play and keep score of everything you've played together."
        prompt="Press start"
    />

    <section class="panel flex w-full flex-[0_1_min(480px,100%)] flex-col gap-5 border-cyan p-9 shadow-[8px_8px_0_#000]">
        <p class="label text-cyan">&gt; Select player</p>
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-yellow min-h-[52px] w-full text-xs">Continue</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-yellow min-h-[52px] w-full text-xs">Log in</a>
            <a href="{{ route('register') }}" class="btn btn-ghost min-h-[52px] w-full text-xs">Create account</a>
        @endauth
    </section>
</x-layouts.guest>
