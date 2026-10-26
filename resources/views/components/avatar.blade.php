@props(['user', 'size' => 40])

@php
    $palette = ['#FFD23E', '#3EF0FF', '#FF5C8A', '#8B7CFF', '#5CFF9D', '#FF8A3D', '#C9C7F0'];
    $initials = collect(preg_split('/\s+/', trim($user->name)))->take(2)->map(fn ($word) => mb_substr($word, 0, 1))->join('');
@endphp

<span
    {{ $attributes->class('box-border flex flex-none items-center justify-center border-3 border-black font-pixel text-ink uppercase') }}
    style="width: {{ $size }}px; height: {{ $size }}px; font-size: {{ max(8, round($size / 4)) }}px; background: {{ $palette[$user->id % count($palette)] }}"
    title="{{ $user->name }}"
>{{ $initials }}</span>
