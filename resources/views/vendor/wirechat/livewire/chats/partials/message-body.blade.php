<div class="flex gap-x-2 items-center">

    {{-- Only show if AUTH is onwer of message --}}
    @if ($belongsToAuth)
        <span class="font-bold text-xs ">
            @lang('wirechat::chats.labels.you'):
        </span>
    @elseif(!$belongsToAuth && $group !== null)
        <span class="font-bold text-xs ">
            {{ $lastMessage->sendable?->display_name }}:
        </span>
    @endif

    <p @class([
        'truncate text-sm gap-2 items-center',
        'font-semibold text-black' =>
            !$isReadByAuth && !$lastMessage?->ownedBy($this->auth),
        'font-normal text-gray-600' =>
            $isReadByAuth && !$lastMessage?->ownedBy($this->auth),
        'font-normal text-gray-600' =>
            $isReadByAuth && $lastMessage?->ownedBy($this->auth),
    ])>
        {{ $lastMessage->body != '' ? $lastMessage->body : ($lastMessage->isAttachment() ? '📎 '.__('wirechat::chats.labels.attachment') : '') }}
    </p>

    <span class="font-medium px-1 text-xs shrink-0 text-gray-800">
        @if ($lastMessage->created_at->diffInMinutes(now()) < 1)
          @lang('wirechat::chats.labels.now')
        @else
            {{ $lastMessage->created_at->shortAbsoluteDiffForHumans() }}
        @endif
    </span>


</div>
