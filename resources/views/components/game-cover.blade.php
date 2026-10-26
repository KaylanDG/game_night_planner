@props(['name', 'imageId' => null, 'seed' => 0, 'size' => 'cover_big', 'label' => false])

@php
    $palette = ['#FF5C8A', '#5CFF9D', '#FF8A3D', '#3EF0FF', '#FFD23E', '#8B7CFF'];
    $short = collect(preg_split('/[\s:\-]+/', $name))->filter()->take(3)->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->join('');
    $url = \App\Services\IgdbService::coverUrl($imageId, $size);
@endphp

@if ($url)
    <img
        src="{{ $url }}"
        alt="{{ $label ? "Cover of {$name}" : '' }}"
        loading="lazy"
        {{ $attributes->class('box-border flex-none border-3 border-black bg-track object-cover') }}
    >
@else
    <div
        @if ($label) role="img" aria-label="Cover of {{ $name }}" @else aria-hidden="true" @endif
        {{ $attributes->class('cover flex-none') }}
        style="background-color: {{ $palette[$seed % count($palette)] }}"
    >{{ $slot->isEmpty() ? $short : $slot }}</div>
@endif
