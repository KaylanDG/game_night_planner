@php
    // Wheel geometry in a 200×200 viewBox centred on the origin. Angles run clockwise from the top.
    $suggestions = $session->suggestions->sortBy('id')->values();
    $count = $suggestions->count();
    $size = $count > 0 ? 360 / $count : 360;
    $radius = 96;
    $palette = ['#FFD23E', '#3EF0FF', '#FF5C8A', '#5CFF9D', '#8B7CFF', '#FF8A3D'];
    $point = fn (float $degrees, float $r) => sprintf('%.3f %.3f', $r * sin(deg2rad($degrees)), -$r * cos(deg2rad($degrees)));
@endphp

<div data-roulette="{{ route('sessions.roulette', $session) }}" class="flex flex-col items-center gap-6">
    @if ($count === 0)
        <div class="flex w-full flex-col items-center gap-3 border-3 border-dashed border-line px-5 py-10 text-center">
            <p class="m-0 font-pixel text-xs text-pink">Nothing to spin</p>
            <p class="m-0 text-2xl text-muted">Suggest a few games for this session first, then spin the wheel to pick one.</p>
        </div>
    @else
        <div class="relative w-full max-w-[min(420px,55vh)] pt-4">
            {{-- Fixed pointer at the top; the wheel turns underneath it. --}}
            <svg aria-hidden="true" class="absolute top-0 left-1/2 z-10 w-9 -translate-x-1/2 drop-shadow-[3px_3px_0_#000]" viewBox="0 0 9 9" shape-rendering="crispEdges">
                <rect x="0" y="0" width="9" height="2" fill="var(--color-text)"/>
                <rect x="1" y="2" width="7" height="2" fill="var(--color-text)"/>
                <rect x="2" y="4" width="5" height="2" fill="var(--color-text)"/>
                <rect x="3" y="6" width="3" height="2" fill="var(--color-text)"/>
                <rect x="4" y="8" width="1" height="1" fill="var(--color-text)"/>
            </svg>

            <div class="aspect-square w-full rounded-full border-3 border-black bg-black shadow-[6px_6px_0_#000]">
                <svg data-roulette-wheel viewBox="-100 -100 200 200" class="roulette-wheel block size-full" role="img" aria-label="Wheel with {{ trans_choice(':count game|:count games', $count) }}">
                    @foreach ($suggestions as $suggestion)
                        @php
                            $start = $loop->index * $size;
                            $centre = $start + $size / 2;
                            // Keep neighbours (including last → first) different colours.
                            $colour = $palette[$loop->index % 6];
                            if ($loop->last && $count > 1 && $count % 6 === 1) {
                                $colour = $palette[2];
                            }
                            $name = $suggestion->game->name;
                            $label = mb_strlen($name) > 18 ? mb_substr($name, 0, 17).'…' : $name;
                            // Fit the label along the radius and within the slice's width.
                            $fontSize = min(8, 74 / max(mb_strlen($label), 1), $count > 1 ? 58 * deg2rad($size) * 0.55 : 8);
                        @endphp
                        <g data-suggestion-id="{{ $suggestion->id }}" class="roulette-segment">
                            @if ($count === 1)
                                <circle r="{{ $radius }}" fill="{{ $colour }}" />
                            @else
                                <path
                                    d="M 0 0 L {{ $point($start, $radius) }} A {{ $radius }} {{ $radius }} 0 {{ $size > 180 ? 1 : 0 }} 1 {{ $point($start + $size, $radius) }} Z"
                                    fill="{{ $colour }}"
                                    stroke="#000"
                                    stroke-width="1.5"
                                />
                            @endif
                            <text
                                transform="rotate({{ $count === 1 ? -90 : $centre - 90 }})"
                                x="{{ $radius - 8 }}"
                                y="0"
                                text-anchor="end"
                                dominant-baseline="central"
                                fill="#0B0B1A"
                                font-size="{{ round($fontSize, 2) }}"
                                class="font-pixel"
                            >{{ $label }}</text>
                        </g>
                    @endforeach
                    <circle r="15" fill="var(--color-ink)" stroke="#000" stroke-width="3" />
                    <circle r="6" fill="var(--color-yellow)" />
                </svg>
            </div>
        </div>

        <ul class="sr-only">
            @foreach ($suggestions as $suggestion)
                <li>{{ $suggestion->game->name }}</li>
            @endforeach
        </ul>

        <div data-roulette-result hidden aria-live="polite" class="w-full">
            <div class="flex items-center gap-5 border-3 border-black bg-yellow p-4 text-ink shadow-[6px_6px_0_#000]">
                <img data-winner-cover hidden alt="" class="h-24 w-[72px] flex-none border-3 border-black object-cover">
                <div data-winner-placeholder hidden class="cover h-24 w-[72px] flex-none bg-pink p-1.5 text-[7px]"></div>
                <div class="flex min-w-0 flex-col gap-2">
                    <p class="m-0 font-pixel text-[10px] uppercase">Tonight you're playing</p>
                    <a data-winner-link href="#" class="font-pixel text-base leading-[1.4] text-ink hover:text-ink"><span data-winner-name></span></a>
                </div>
            </div>
        </div>

        <p data-roulette-error hidden role="alert" class="field-error"></p>

        <button type="button" data-roulette-spin class="btn btn-yellow w-full max-w-[420px] py-4 text-xs">Spin the wheel</button>
    @endif
</div>
