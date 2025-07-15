<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\{ Auth, Log };
use Namu\WireChat\Models\{ Conversation, Message, Participant, Group };
use Namu\WireChat\Enums\ConversationType;
use App\Models\User;
use Carbon\Carbon;

class ChatApiIntegrationController extends Controller
{
    /**
     * Retorna as mensagens recentes para o usuário autenticado para exibição no dropdown de notificações.
     * Esta API será consumida pela aplicação principal.
     */
    public function getRecentMessagesForNotifications(Request $request, User $user)
    {
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        /**
         * Recupera as conversas das quais o usuário é participante, ordenadas pela última atividade.
         * Inclui a última mensagem e os participantes para determinar o nome da conversa e o remetente da última mensagem.
         */
        $conversations = Conversation::whereHas('participants', function ($query) use ($user) {
            $query->where('participantable_id', $user->id)
                  ->where('participantable_type', get_class($user))
                  ->whereNull('conversation_deleted_at');
        })
        ->with(['lastMessage.sendable', 'participants.participantable', 'group'])
        ->orderByDesc('updated_at')
        ->take(4)
        ->get();

        $formattedMessages = $conversations->map(function ($conversation) use ($user) {
            $lastMessage = $conversation->lastMessage;
            $unreadCount = 0;
            $conversationName = '';
            $messagePreview = $lastMessage ? $lastMessage->body : 'Nenhuma mensagem.';
            $timeAgo = $lastMessage ? Carbon::parse($lastMessage->created_at)->diffForHumans(null, true) : '';

            // Lógica para contar mensagens não lidas
            $participant = $conversation->participants->where('participantable_id', $user->id)
                                                     ->where('participantable_type', get_class($user))
                                                     ->first();

            if ($participant) {
                // Não contar mensagens enviadas pelo próprio usuário
                if ($participant->conversation_read_at) {
                    $unreadCount = $conversation->messages()
                                                ->where('created_at', '>', $participant->conversation_read_at)
                                                ->where('sendable_id', '!=', $user->id)
                                                ->count();
                } else {
                    // Se nunca leu, todas as mensagens são não lidas (excluindo as próprias)
                    $unreadCount = $conversation->messages()
                                                ->where('sendable_id', '!=', $user->id)
                                                ->count();
                }
            }

            // Determinar o nome da conversa
            if ($conversation->type === ConversationType::PRIVATE) {
                $otherParticipant = $conversation->participants->first(function ($p) use ($user) {
                    return $p->participantable_id !== $user->id || $p->participantable_type !== get_class($user);
                });

                // Garante que o participante exista antes de tentar acessar 'participantable'
                $conversationName = $otherParticipant && $otherParticipant->participantable
                                        ? ($otherParticipant->participantable->display_name ?? $otherParticipant->participantable->name ?? 'Chat Privado')
                                        : 'Chat Privado';

            } elseif ($conversation->type === ConversationType::GROUP) {
                $conversationName = $conversation->group->name;

                // Se a última mensagem for de um usuário específico em um grupo
                if ($lastMessage && $lastMessage->sendable) {
                    $messagePreview = ($lastMessage->sendable->display_name ?? $lastMessage->sendable->name ?? 'Usuário') . ': ' . $messagePreview;
                }
            } else {
                $conversationName = 'Chat';
            }

            // Gerar a URL do chat para a aplicação de chat externa
            $chatUrl = 'http://localhost:81/chats/' . $conversation->id;

            return [
                'conversation_id' => $conversation->id,
                'conversation_name' => $conversationName,
                'last_message' => $messagePreview,
                'last_message_sender' => ($lastMessage && $lastMessage->sendable && $lastMessage->sendable->id === $user->id) ? 'Você' : '',
                'last_message_time' => $timeAgo,
                'unread_count' => $unreadCount,
                'chat_url' => $chatUrl,
            ];
        });

        return response()->json(['formatted_messages' => $formattedMessages]);
    }

    /**
     * Marca uma conversa específica como lida para o usuário autenticado.
     * Esta API será consumida pela aplicação principal.
     */
    public function markConversationAsRead(Conversation $conversation, User $user)
    {
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $participant = $conversation->participants()->where('participantable_id', $user->id)
                                    ->where('participantable_type', get_class($user))
                                    ->first();

        if ($participant) {
            $participant->conversation_read_at = Carbon::now();
            $participant->save();
            return response()->json(['message' => 'Conversation marked as read.']);
        }

        return response()->json(['message' => 'Participant not found in this conversation.'], 404);
    }

    /**
     * Retorna o total de mensagens não lidas para o usuário autenticado.
     * Usado para o contador global de notificações no ícone do chat.
     */
    public function getTotalUnreadMessagesCount(User $user)
    {
        if (!$user) {
            return response()->json(['total_unread' => 0], 200);
        }

        $totalUnread = 0;

        // Recuperar as conversas ativas do usuário
        $conversations = Conversation::whereHas('participants', function ($query) use ($user) {
            $query->where('participantable_id', $user->id)
                  ->where('participantable_type', get_class($user))
                  ->whereNull('conversation_deleted_at');
        })
        ->with(['messages', 'participants' => function($query) use ($user) {
            $query->where('participantable_id', $user->id)
                  ->where('participantable_type', get_class($user));
        }])
        ->get();

        foreach ($conversations as $conversation) {
            $participant = $conversation->participants->first();

            if ($participant) {
                if ($participant->conversation_read_at) {
                    $unreadInConversation = $conversation->messages()
                                                        ->where('created_at', '>', $participant->conversation_read_at)
                                                        ->where('sendable_id', '!=', $user->id)
                                                        ->count();
                } else {
                    $unreadInConversation = $conversation->messages()
                                                        ->where('sendable_id', '!=', $user->id)
                                                        ->count();
                }
                $totalUnread += $unreadInConversation;
            }
        }

        return response()->json(['total_unread' => $totalUnread]);
    }
}