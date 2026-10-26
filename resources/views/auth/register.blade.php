<x-layouts.guest title="Register">
    <x-auth-hero
        heading="PLAYER<br>2 JOINS"
        tagline="Create your account to RSVP for game nights, suggest games and see what the group has played."
        prompt="Insert coin"
        accent="cyan"
    />

    <section class="panel flex w-full flex-[0_1_min(500px,100%)] flex-col gap-6 border-yellow p-9 shadow-[8px_8px_0_#000]" aria-labelledby="register-title">
        <div class="flex flex-col gap-3">
            <p class="label text-yellow">&gt; New player</p>
            <h1 id="register-title" class="m-0 font-pixel text-[22px] leading-[1.4]">Create account</h1>
        </div>

        <form method="POST" action="{{ route('register') }}" class="m-0 flex flex-col gap-[18px]">
            @csrf

            <div class="flex flex-col gap-2">
                <label for="name" class="label text-text">Name</label>
                <input id="name" class="field" type="text" name="name" value="{{ old('name') }}" placeholder="What should friends call you?" required autofocus autocomplete="name" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                @error('name')
                    <p id="name-error" class="field-error"><x-icon name="alert" />{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="email" class="label text-text">Email</label>
                <input id="email" class="field" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autocomplete="username" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <p id="email-error" class="field-error"><x-icon name="alert" />{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="password" class="label text-text">Password</label>
                <div class="relative">
                    <input id="password" class="field pr-[84px]" type="password" name="password" data-password required autocomplete="new-password" aria-describedby="password-rules" @error('password') aria-invalid="true" @enderror>
                    <button type="button" class="absolute top-1.5 right-1.5 h-11 min-w-16 cursor-pointer border-3 border-edge bg-panel px-2.5 font-pixel text-[8px] text-text uppercase hover:border-yellow hover:text-yellow" data-password-toggle aria-pressed="false" aria-label="Show password">Show</button>
                </div>
                @error('password')
                    <p class="field-error"><x-icon name="alert" />{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="label text-text">Confirm password</label>
                <input id="password_confirmation" class="field" type="password" name="password_confirmation" data-password required autocomplete="new-password" aria-describedby="password-rules">
            </div>

            <ul id="password-rules" data-password-rules="8" aria-live="polite" class="m-0 flex list-none flex-col gap-2 border-3 border-dashed border-line bg-night px-4 py-3.5">
                @foreach (['length' => 'At least 8 characters', 'match' => 'Passwords match'] as $rule => $text)
                    <li data-rule="{{ $rule }}" data-state="idle" class="group/rule flex items-center gap-3 text-[22px] text-muted data-[state=bad]:text-orange data-[state=ok]:text-green">
                        <span data-mark aria-hidden="true" class="flex size-[22px] flex-none items-center justify-center border-2 border-black bg-edge font-pixel text-[8px] text-ink group-data-[state=bad]/rule:bg-orange group-data-[state=ok]/rule:bg-green">·</span>
                        <span>{{ $text }}</span><span data-sr class="sr-only"></span>
                    </li>
                @endforeach
            </ul>

            <button type="submit" class="btn btn-yellow min-h-[52px] w-full px-5 py-4 text-xs">Create account</button>
        </form>

        <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 border-t-3 border-dashed border-line pt-[18px]">
            <span class="text-2xl text-muted">Already playing?</span>
            <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center font-pixel text-[10px] uppercase">Log in</a>
        </div>
    </section>
</x-layouts.guest>
