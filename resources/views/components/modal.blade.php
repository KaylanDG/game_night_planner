@props(['id', 'title', 'accent' => 'yellow', 'open' => false, 'closable' => true])

<dialog
    id="{{ $id }}"
    aria-labelledby="{{ $id }}-title"
    @if ($open) data-open @endif
    {{ $attributes->class(['modal', 'border-yellow' => $accent === 'yellow', 'border-green' => $accent === 'green']) }}
>
    <div class="flex flex-col gap-[22px]">
        <div class="flex items-start justify-between gap-4">
            <div class="flex flex-col gap-2.5">
                <h2 id="{{ $id }}-title" @class(['m-0 font-pixel text-base leading-[1.4]', 'text-yellow' => $accent === 'yellow', 'text-green' => $accent === 'green'])>{{ $title }}</h2>
                @isset($description)
                    <p class="m-0 text-2xl text-muted">{{ $description }}</p>
                @endisset
            </div>
            @if ($closable)
                <button type="button" aria-label="Close" data-dialog-close class="flex size-11 flex-none cursor-pointer items-center justify-center border-3 border-edge bg-panel text-text hover:border-yellow hover:text-yellow">
                    <x-icon name="close" :size="14" />
                </button>
            @endif
        </div>

        {{ $slot }}
    </div>
</dialog>
