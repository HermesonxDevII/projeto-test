<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Namu\WireChat\Models\Message;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewMessageNotification extends Notification implements ShouldBroadcastNow
{
// use Queueable;

    /**
     * Create a new notification instance.
     */
    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;

        //  Explicitly set the connection to sync
        //  $this->onConnection = 'sync';
        //  $this->onConnection('sync');
        //  $this->onQueue(WireChat::notificationsQueue());
        //  $this->delay(now()->addSeconds(2)); // Delay the job by 5 seconds
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast'];

    }

    /**
     * Determine if the notification should be sent.
     * Only send if the message is less than 3 minutes old.
     */
    // public function shouldSend(object $notifiable, string $channel): bool
    // {
    //    /// dd($this->message->created_at->greaterThan(Carbon::now()->subMinutes(3)));
    //     return $this->message->created_at->greaterThan(Carbon::now()->subMinutes(3));
    // }

    // /**
    //  * Get the channels the event should broadcast on.
    //  *
    //  * @return array<int, \Illuminate\Broadcasting\PrivateChannel>
    //  */
    // public function broadcastOn(): array
    // {
    //     return [
    //         new PrivateChannel('conversation.'.$this->message->conversation_id)
    //     ];
    // }

    // Broadcast data for real-time notifications
    public function toBroadcast($notifiable)
    {

        return new BroadcastMessage([
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
        ]);
    }
}
