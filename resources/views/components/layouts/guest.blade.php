@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? "{$title} · " : '' }}{{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="scanlines flex min-h-screen flex-col">
            <main class="mx-auto box-border flex w-full max-w-[1180px] flex-1 flex-wrap items-center justify-center gap-x-[72px] gap-y-14 px-6 py-14">
                {{ $slot }}
            </main>

            <footer class="border-t-3 border-line p-6 text-center">
                <p class="label text-[9px]">Private party · Friends only · <span class="text-yellow">Credit 00</span></p>
            </footer>
        </div>
    </body>
</html>
