@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? "{$title} · " : '' }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="scanlines flex min-h-screen flex-col">
            <header class="border-b-3 border-line bg-night">
                <div class="mx-auto box-border flex max-w-[1280px] flex-wrap items-center gap-x-8 gap-y-3 px-6 py-3.5">
                    <a href="{{ route('dashboard') }}" class="flex min-h-11 items-center gap-3 text-yellow no-underline hover:text-yellow">
                        <x-icon name="logo" :size="32" />
                        <span class="font-pixel text-[15px] tracking-[1px]">GAME NIGHT</span>
                    </a>

                    <nav aria-label="Main" class="flex flex-auto flex-wrap gap-2">
                        @foreach (['dashboard' => 'Home', 'games.*' => 'Games', 'sessions.*' => 'Sessions'] as $pattern => $label)
                            <a
                                class="nav-link"
                                href="{{ route(str_replace('.*', '.index', $pattern)) }}"
                                @if (request()->routeIs($pattern)) aria-current="page" @endif
                            >{{ $label }}</a>
                        @endforeach
                    </nav>

                    <div class="flex items-center gap-3">
                        <x-avatar :user="auth()->user()" :size="40" />
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="nav-link cursor-pointer bg-transparent">Log out</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="mx-auto box-border flex w-full max-w-[1280px] flex-1 flex-col gap-8 px-6 pt-10 pb-16">
                {{ $slot }}
            </main>

            <footer class="border-t-3 border-line p-6 text-center">
                <p class="label text-[9px]">
                    Private party · {{ trans_choice(':count player|:count players', $memberCount) }} ·
                    <span class="blink text-yellow">Insert coin</span>
                </p>
            </footer>
        </div>
    </body>
</html>
