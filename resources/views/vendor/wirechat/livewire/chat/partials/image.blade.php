@php
    $isSameAsNext = ($message?->sendable_id === $nextMessage?->sendable_id) && ($message?->sendable_type === $nextMessage?->sendable_type);
    $isNotSameAsNext = !$isSameAsNext;
    $isSameAsPrevious = ($message?->sendable_id === $previousMessage?->sendable_id) && ($message?->sendable_type === $previousMessage?->sendable_type);
    $isNotSameAsPrevious = !$isSameAsPrevious;
@endphp

<div
    x-data="{ open: false }"
    class="relative"
>
    <img 
        @click="$dispatch('open-image', { src: '{{ $attachment?->url }}' })"
        class="cursor-pointer h-[200px] object-scale-down rounded-xl"
        src="{{ $attachment?->url }}"
        alt="Imagem"
    />
</div>