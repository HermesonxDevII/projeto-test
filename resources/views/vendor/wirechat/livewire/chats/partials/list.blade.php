
@use('Namu\WireChat\Facades\WireChat')

<ul wire:loading.delay.long.remove wire:target="search" class="p-2 grid w-full spacey-y-2">
    @foreach ($conversations as $key=> $conversation)
        @php
            $receiver = null;

            $group = $conversation->isGroup() ? $conversation->group : null;

            $lastMessage = $conversation->lastMessage;

            //mark isReadByAuth true if user has chat opened
            $isReadByAuth = $conversation?->readBy($conversation->authParticipant??$this->auth) || $selectedConversationId == $conversation->id;

            $belongsToAuth = $lastMessage?->belongsToAuth();

            if ($conversation->isPrivate() && !$conversation->isSelfConversation()) {
                $auth = $this->auth;

                $receiverParticipant = $conversation->participants->first(
                    fn($p) => $p->participantable_id !== $auth->id || $p->participantable_type !== get_class($auth)
                );

                $receiver = $receiverParticipant?->participantable;
            }

            $unreadCount = $this->auth ? $conversation->getUnreadCountFor($this->auth) : 0;
        @endphp
        <li
            x-data="{
                conversationID: @js($conversation->id),
                showUnreadStatus: @js(!$isReadByAuth),
                handleChatOpened(event) {
                    // Hide unread dot
                    if (event.detail.conversation== this.conversationID) {
                        this.showUnreadStatus= false;
                    }
                    //update this so that the the selected conversation highlighter can be updated
                    $wire.selectedConversationId= event.detail.conversation;
                },
                handleChatClosed(event) {
                        // Clear the globally selected conversation.
                        $wire.selectedConversationId = null;
                        selectedConversationId = null;
                },
                handleOpenChat(event) {
                    // Clear the globally selected conversation.
                    if (this.showUnreadStatus==  event.detail.conversation== this.conversationID) {
                        this.showUnreadStatus= false;
                    }
                }
            }"  
            id="conversation-{{ $conversation->id }}" 
            wire:key="conversation-em-{{ $conversation->id }}-{{ $conversation->updated_at->timestamp }}"
            x-on:chat-opened.window="handleChatOpened($event)"
            x-on:chat-closed.window="handleChatClosed($event)"
            <a @if ($widget) tabindex="0" 
            role="button" 
            dusk="openChatWidgetButton"
            @click="$dispatch('open-chat',{conversation:'@json($conversation->id)'})"
            @keydown.enter="$dispatch('open-chat',{conversation:'@json($conversation->id)'})"
            @else
            wire:navigate href="{{ route(WireChat::viewRouteName(), $conversation->id) }}" @endif
                @style(['border-color:var(--wirechat-primary-color)' => $selectedConversationId == $conversation?->id])
                class="py-3 flex gap-4 hover:bg-gray-50 rounded-xs transition-colors duration-150  relative w-full cursor-pointer px-2"
                :class="$wire.selectedConversationId == conversationID &&
                    'bg-gray-50 border-r-4  border-opacity-20 border-[var(--wirechat-primary-color)]'">

                <div class="shrink-0">
                    <x-wirechat::avatar
                        disappearing="{{ $conversation->hasDisappearingTurnedOn() }}"
                        group="{{ $conversation->isGroup() }}"
                        src="{{ $group ? $group?->cover_url : $receiver?->cover_url ?? null }}" class="w-12 h-12"
                    />
                </div>

                <aside class="grid  grid-cols-12 w-full">
                    <div class="col-span-10 border-b pb-2 border-gray-100 relative overflow-hidden truncate leading-5 w-full flex-nowrap p-1">
                        {{-- name --}}
                        <div class="flex gap-1 mb-1 w-full items-center">
                            <h6 class="truncate font-medium text-gray-900 ">
                                {{ $group ? $group?->name : $receiver?->display_name }}
                            </h6>

                            @if ($conversation->isSelfConversation())
                                <span class="font-medium">({{__('wirechat::chats.labels.you')  }})</span>
                            @endif

                        </div>
                        {{-- Message body --}}
                        @if ($lastMessage != null)
                            @include('wirechat::livewire.chats.partials.message-body')
                        @endif
                    </div>

                    {{-- Read status --}}
                    {{-- Only show if AUTH is NOT onwer of message --}}
                    @if ($lastMessage != null && !$lastMessage?->ownedBy($this->auth) && !$isReadByAuth)
                        <div
                            x-show="showUnreadStatus"
                            dusk="unreadMessagesDot"
                            class="col-span-2 flex flex-col text-center my-auto"
                        >
                            {{-- Dots icon --}}
                            <div class="col-span-2 flex flex-col justify-center items-center my-auto">
                                <span
                                    class="text-white rounded-full h-5 w-5 flex items-center justify-center"
                                    style="background-color: #329FBA;"
                                >
                                    {{ $unreadCount }}
                                </span>
                            </div>
                        </div>
                    @endif
                </aside>
            </a>

        </li>
    @endforeach
</ul>
