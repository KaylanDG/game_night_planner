@csrf

<div class="flex flex-col gap-[18px]">
    <div class="flex flex-col gap-2">
        <label for="{{ $idPrefix ?? '' }}title" class="label text-text">Session name <span class="text-muted">(optional)</span></label>
        <input id="{{ $idPrefix ?? '' }}title" class="field" type="text" name="title" maxlength="255" placeholder="{{ \App\Models\GameSession::DEFAULT_TITLE }}" value="{{ old('title', $session?->title) }}" @error('title') aria-invalid="true" @enderror>
        @error('title') <p class="field-error"><x-icon name="alert" />{{ $message }}</p> @enderror
    </div>

    <div class="flex flex-wrap gap-4">
        <div class="flex flex-[1_1_180px] flex-col gap-2">
            <label for="{{ $idPrefix ?? '' }}date" class="label text-text">Date <span class="text-yellow">*</span></label>
            <input id="{{ $idPrefix ?? '' }}date" class="field" type="date" name="date" required value="{{ old('date', $session?->starts_at->format('Y-m-d')) }}" @error('date') aria-invalid="true" @enderror>
        </div>
        <div class="flex flex-[1_1_140px] flex-col gap-2">
            <label for="{{ $idPrefix ?? '' }}start_time" class="label text-text">Start time <span class="text-yellow">*</span></label>
            <input id="{{ $idPrefix ?? '' }}start_time" class="field" type="time" name="start_time" required value="{{ old('start_time', $session?->starts_at->format('H:i')) }}" @error('start_time') aria-invalid="true" @enderror>
        </div>
    </div>

    @error('date') <p class="field-error"><x-icon name="alert" />{{ $message }}</p> @enderror
    @error('start_time') <p class="field-error"><x-icon name="alert" />{{ $message }}</p> @enderror
</div>
