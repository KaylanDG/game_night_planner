@props(['heading', 'tagline', 'prompt', 'accent' => 'yellow'])

<div class="flex min-w-0 flex-[1_1_min(400px,100%)] flex-col items-start gap-7">
    <x-icon
        name="logo"
        :size="176"
        class="drop-shadow-[8px_8px_0_#000]"
        :style="$accent === 'cyan' ? '--logo-body: var(--color-cyan); --logo-accent: var(--color-yellow)' : null"
    />
    <p @class(['m-0 font-pixel text-4xl leading-[1.3] text-shadow-[5px_5px_0_#000]', 'text-yellow' => $accent === 'yellow', 'text-cyan' => $accent === 'cyan'])>{!! $heading !!}</p>
    <p class="m-0 max-w-[420px] text-[28px] leading-[1.3] text-soft">{{ $tagline }}</p>
    <p @class(['m-0 font-pixel text-xs', 'text-cyan' => $accent === 'yellow', 'text-yellow' => $accent === 'cyan'])><span class="blink">{{ $prompt }}</span></p>
</div>
