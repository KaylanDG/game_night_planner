@props(['date', 'size' => 'md'])

@php
    [$width, $month, $day, $weekday] = match ($size) {
        'lg' => ['w-28', 'text-[11px] py-2', 'text-[34px] pt-3.5 pb-2', 'text-[10px] pb-3'],
        'sm' => ['w-14', 'text-[8px] py-1', 'text-base py-1.5', null],
        default => ['w-[76px]', 'text-[9px] py-1.5', 'text-2xl pt-2.5 pb-1.5', 'text-[8px] pb-2'],
    };
@endphp

<div {{ $attributes->class([$width, 'flex-none border-3 border-black bg-text text-center font-pixel text-ink', 'shadow-[3px_3px_0_#000]' => $size !== 'sm']) }} aria-hidden="true">
    <div class="{{ $month }} border-b-3 border-black bg-pink uppercase">{{ $date->format('M') }}</div>
    <div class="{{ $day }}">{{ $date->format('d') }}</div>
    @if ($weekday)
        <div class="{{ $weekday }} uppercase">{{ $date->format('D') }}</div>
    @endif
</div>
