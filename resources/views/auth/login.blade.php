<x-layouts.guest title="Log in">
    <x-auth-hero
        heading="GAME<br>NIGHT"
        tagline="Plan game nights, pick what to play and keep score of everything you've played together."
        prompt="Press start"
    />

    <section class="panel flex w-full flex-[0_1_min(480px,100%)] flex-col gap-[26px] border-cyan p-9 shadow-[8px_8px_0_#000]" aria-labelledby="login-title">
        <div class="flex flex-col gap-3">
            <p class="label text-cyan">&gt; Player 1</p>
            <h1 id="login-title" class="m-0 font-pixel text-[22px] leading-[1.4]">Log in</h1>
            <p class="m-0 text-2xl text-muted">Welcome back. Your party is waiting.</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="m-0 flex flex-col gap-5">
            @csrf

            <div class="flex flex-col gap-2">
                <label for="email" class="label text-text">Email</label>
                <input id="email" class="field" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="username" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <p id="email-error" class="field-error"><x-icon name="alert" />{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="label text-text">Password</label>
                <div class="relative">
                    <input id="password" class="field pr-[84px]" type="password" name="password" data-password placeholder="••••••••" required autocomplete="current-password" @error('password') aria-invalid="true" @enderror>
                    <button type="button" class="absolute top-1.5 right-1.5 h-11 min-w-16 cursor-pointer border-3 border-edge bg-panel px-2.5 font-pixel text-[8px] text-text uppercase hover:border-yellow hover:text-yellow" data-password-toggle aria-pressed="false" aria-label="Show password">Show</button>
                </div>
                @error('password')
                    <p class="field-error"><x-icon name="alert" />{{ $message }}</p>
                @enderror
            </div>

            <label class="flex min-h-11 cursor-pointer items-center gap-3 text-2xl text-soft">
                <input type="checkbox" name="remember" class="size-5 accent-yellow" @checked(old('remember'))>
                Remember me
            </label>

            <button type="submit" class="btn btn-yellow min-h-[52px] w-full px-5 py-4 text-xs">Log in</button>
        </form>

        <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 border-t-3 border-dashed border-line pt-5">
            <span class="text-2xl text-muted">New player?</span>
            <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center font-pixel text-[10px] uppercase">Create an account</a>
        </div>
    </section>
</x-layouts.guest>
