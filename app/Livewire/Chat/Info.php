<?php

namespace App\Livewire\Chat;

use Livewire\Attributes\Locked;
use Namu\WireChat\Facades\WireChat;
use App\Livewire\Chats\Chats;
use Namu\WireChat\Livewire\Concerns\ModalComponent;
use Namu\WireChat\Livewire\Concerns\Widget;
use App\Models\Conversation;

class Info extends ModalComponent
{
    use Widget;

    #[Locked]
    public Conversation $conversation;

    public static function closeModalOnEscapeIsForceful(): bool
    {
        return false;
    }

    /**
     * -----------------------------
     * Delete Chat
     * */
    public function deleteChat()
    {
        abort_unless(getAuthenticatedParticipant(), 401);

        abort_unless(getAuthenticatedParticipant()->belongsToConversation($this->conversation), 403);
        abort_unless($this->conversation->isSelf() || $this->conversation->isPrivate(), 403, 'This operation is not available for Groups.');

        // delete conversation
        $this->conversation->deleteFor(getAuthenticatedParticipant());

        // redirect to chats page pr
        // Dispatach event instead if isWidget
        // handle widget termination
        $this->handleComponentTermination(
            redirectRoute: route(WireChat::indexRouteName()),
            events: [
                'close-chat',
                Chats::class => ['chat-deleted',  [$this->conversation->id]],
            ]
        );

    }

    public function placeholder()
    {
        return <<<'HTML'
        <div>
            <!-- Loading spinner... -->
            <x-wirechat::loading-spin class="m-auto" />
        </div>
        HTML;
    }

    public function mount()
    {

        abort_if(empty($this->conversation), 404);

        abort_unless(getAuthenticatedParticipant(), 401);
        abort_unless(getAuthenticatedParticipant()->belongsToConversation($this->conversation), 403);

        abort_if($this->conversation->isGroup(), 403, __('wirechat::chat.info.messages.invalid_conversation_type_error'));

    }

    public function render()
    {

        $cover_url = $this->conversation->getReceiver()?->cover_url;

        // Pass data to the view
        return view('wirechat::livewire.chat.info', [
            'receiver' => $this->conversation?->getReceiver(),
            'cover_url' => $cover_url,
        ]);
    }
}
