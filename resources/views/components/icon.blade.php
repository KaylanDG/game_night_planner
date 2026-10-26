@props(['name', 'size' => 12])

{{-- Pixel-art icons drawn on a small grid; decorative, so hidden from assistive tech. --}}
@switch($name)
    @case('logo')
        <svg {{ $attributes }} aria-hidden="true" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 16 16" shape-rendering="crispEdges">
            <rect x="1" y="5" width="14" height="7" fill="var(--logo-body, var(--color-yellow))"/>
            <rect x="2" y="12" width="3" height="2" fill="var(--logo-body, var(--color-yellow))"/>
            <rect x="11" y="12" width="3" height="2" fill="var(--logo-body, var(--color-yellow))"/>
            <rect x="3" y="8" width="3" height="1" fill="var(--color-ink)"/>
            <rect x="4" y="7" width="1" height="3" fill="var(--color-ink)"/>
            <rect x="10" y="7" width="1" height="1" fill="var(--color-ink)"/>
            <rect x="12" y="9" width="1" height="1" fill="var(--color-ink)"/>
            <rect x="7" y="3" width="2" height="2" fill="var(--logo-accent, var(--color-cyan))"/>
        </svg>
        @break
    @case('plus')
        <svg {{ $attributes }} aria-hidden="true" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 8 8" shape-rendering="crispEdges" fill="currentColor">
            <rect x="3" y="1" width="2" height="6"/><rect x="1" y="3" width="6" height="2"/>
        </svg>
        @break
    @case('arrow-right')
        <svg {{ $attributes }} aria-hidden="true" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 8 8" shape-rendering="crispEdges" fill="currentColor">
            <rect x="2" y="1" width="1" height="6"/><rect x="3" y="2" width="1" height="4"/><rect x="4" y="3" width="1" height="2"/>
        </svg>
        @break
    @case('arrow-left')
        <svg {{ $attributes }} aria-hidden="true" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 8 8" shape-rendering="crispEdges" fill="currentColor">
            <rect x="5" y="1" width="1" height="6"/><rect x="4" y="2" width="1" height="4"/><rect x="3" y="3" width="1" height="2"/>
        </svg>
        @break
    @case('close')
        <svg {{ $attributes }} aria-hidden="true" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 7 7" shape-rendering="crispEdges" fill="currentColor">
            <rect x="0" y="0" width="1" height="1"/><rect x="1" y="1" width="1" height="1"/><rect x="2" y="2" width="1" height="1"/>
            <rect x="3" y="3" width="1" height="1"/><rect x="4" y="4" width="1" height="1"/><rect x="5" y="5" width="1" height="1"/>
            <rect x="6" y="6" width="1" height="1"/><rect x="6" y="0" width="1" height="1"/><rect x="5" y="1" width="1" height="1"/>
            <rect x="4" y="2" width="1" height="1"/><rect x="2" y="4" width="1" height="1"/><rect x="1" y="5" width="1" height="1"/>
            <rect x="0" y="6" width="1" height="1"/>
        </svg>
        @break
    @case('search')
        <svg {{ $attributes }} aria-hidden="true" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 10 10" shape-rendering="crispEdges" fill="currentColor">
            <rect x="2" y="0" width="4" height="1"/><rect x="1" y="1" width="1" height="1"/><rect x="6" y="1" width="1" height="1"/>
            <rect x="0" y="2" width="1" height="4"/><rect x="7" y="2" width="1" height="4"/><rect x="1" y="6" width="1" height="1"/>
            <rect x="6" y="6" width="1" height="1"/><rect x="2" y="7" width="4" height="1"/><rect x="7" y="7" width="1" height="1"/>
            <rect x="8" y="8" width="1" height="1"/><rect x="9" y="9" width="1" height="1"/>
        </svg>
        @break
    @case('thumb-up')
        <svg {{ $attributes }} aria-hidden="true" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 8 8" shape-rendering="crispEdges" fill="currentColor">
            <rect x="3" y="0" width="1" height="1"/><rect x="2" y="1" width="2" height="2"/><rect x="0" y="3" width="2" height="5"/>
            <rect x="2" y="3" width="6" height="2"/><rect x="2" y="5" width="5" height="2"/><rect x="2" y="7" width="4" height="1"/>
        </svg>
        @break
    @case('thumb-down')
        <svg {{ $attributes }} aria-hidden="true" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 8 8" shape-rendering="crispEdges" fill="currentColor">
            <rect x="3" y="7" width="1" height="1"/><rect x="2" y="5" width="2" height="2"/><rect x="0" y="0" width="2" height="5"/>
            <rect x="2" y="3" width="6" height="2"/><rect x="2" y="1" width="5" height="2"/><rect x="2" y="0" width="4" height="1"/>
        </svg>
        @break
    @case('alert')
        <svg {{ $attributes }} aria-hidden="true" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 4 4" shape-rendering="crispEdges" fill="currentColor">
            <rect x="1" y="0" width="2" height="2"/><rect x="1" y="3" width="2" height="1"/>
        </svg>
        @break
@endswitch
