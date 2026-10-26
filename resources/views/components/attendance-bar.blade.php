@props(['going', 'notGoing', 'unconfirmed', 'height' => 'h-[22px]'])

<div
    role="img"
    aria-label="{{ $going }} going, {{ $notGoing }} can't make it, {{ $unconfirmed }} unconfirmed"
    {{ $attributes->class('flex gap-1.5') }}
>
    @foreach ([[$going, 'bg-cyan'], [$notGoing, 'bg-orange'], [$unconfirmed, 'bg-track']] as [$count, $color])
        @for ($i = 0; $i < $count; $i++)
            <div class="{{ $height }} {{ $color }} box-border flex-1 border-3 border-black"></div>
        @endfor
    @endforeach
</div>
