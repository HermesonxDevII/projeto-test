<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Namu\WireChat\Enums\Actions;
use Namu\WireChat\Enums\MessageType;
use Namu\WireChat\Facades\WireChat;
use App\Models\Scopes\WithoutRemovedMessages;
use App\Traits\Actionable;

class Message extends Model
{
    use Actionable;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'body',
        'sendable_type',
        'sendable_id',
        'conversation_id',
        'reply_id',
        'type',
        'kept_at',
    ];

    protected $casts = [
        'type' => MessageType::class,
        'kept_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = WireChat::formatTableName('messages');

        parent::__construct($attributes);
    }

    /* relationship */

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /* Polymorphic relationship for the sender */
    public function sendable()
    {
        return $this->morphTo();
    }

    /**
     * since you have a non-standard namespace;
     * the resolver cannot guess the correct namespace for your Factory class.
     * so we exlicilty tell it the correct namespace
     */
    protected static function newFactory()
    {
        return \Namu\WireChat\Workbench\Database\Factories\MessageFactory::new();
    }

    protected static function booted()
    {
        // Add scope if authenticated
        static::addGlobalScope(WithoutRemovedMessages::class);

        // listen to deleted
        static::deleted(function ($message) {

            if ($message->attachment?->exists()) {

                // delete attachment
                $message->attachment?->delete();

                // also delete from storage
                if (Storage::disk(config('wirechat.attachments.storage_disk', 'public'))->exists($message->attachment->file_path)) {
                    Storage::disk(config('wirechat.attachments.storage_disk', 'public'))->delete($message->attachment->file_path);
                }
            }

            // Use a DB transaction to ensure atomicity
            DB::transaction(function () use ($message) {
                // Delete associated actions (polymorphic actionable relation)
                $message->actions()->delete();
            });
        });
    }

    public function attachment()
    {
        return $this->morphOne(Attachment::class, 'attachable');
    }

    public function hasAttachment(): bool
    {
        return $this->attachment()->exists();
    }

    public function isAttachment(): bool
    {
        return $this->type == MessageType::ATTACHMENT;
    }

    /**
     * Check if the message has been read by a specific user.
     */
    public function readBy(Model|Participant $user): bool
    {
        if ($user instanceof Participant) {
            $user = $user->participantable;
        }

        return $this->conversation->getUnreadCountFor($user) <= 0;
    }

    /**
     * Check if the message is owned by user
     */
    public function ownedBy($user): bool
    {
        if (! $user || ! ($user instanceof \Illuminate\Database\Eloquent\Model)) {
            return false;
        }

        return $this->sendable_type == $user->getMorphClass() && $this->sendable_id == $user->id;
    }

    public function belongsToAuth(): bool
    {
        $user = getAuthenticatedParticipant();

        return $this->sendable_type == $user->getMorphClass() && $this->sendable_id == $user->id;
    }

    // Relationship for the parent message
    public function parent()
    {
        return $this->belongsTo(Message::class, 'reply_id')->withoutGlobalScope(WithoutRemovedMessages::class)->withTrashed();
    }

    // Relationship for the reply
    public function reply()
    {
        return $this->hasOne(Message::class, 'reply_id');
    }

    // Method to check if the message has a reply
    public function hasReply(): bool
    {
        return $this->reply()->exists();
    }

    // Method to check if the message has a parent
    public function hasParent(): bool
    {
        return $this->parent()->exists();
    }

    public function scopeWhereIsNotOwnedBy($query, $user)
    {

        $query->where(function ($query) use ($user) {
            $query->where('sendable_id', '<>', $user->id)
                ->orWhere('sendable_type', '<>', $user->getMorphClass());
        });

        // $query->where(function ($query) use ($user) {
        //     $query->whereNot('sendable_id', $user->id)
        //           ->orWhereNot('sendable_type', $user->getMorphClass());
        // });

    }

    /**
     * Delete for
     * This will delete the message only for the auth user meaning other participants will be able to see it
     */
    public function deleteFor($user)
    {
        if (! $user || ! ($user instanceof \Illuminate\Database\Eloquent\Model)) {
            return false;
        }

        $conversation = $this->conversation;

        // Make sure auth belongs to conversation for this message
        abort_unless($user->belongsToConversation($conversation), 403);

        // If conversation is self, then delete permanently directly
        if ($conversation->isSelf()) {
            $this->forceDelete();

            return;
        }

        // Try to create an action
        $this->actions()->create([
            'actor_id' => $user->id,
            'actor_type' => $user->getMorphClass(),
            'type' => Actions::DELETE,
        ]);

        // If it's a private conversation (only 2 users), then check if both users have deleted the message
        if ($conversation->isPrivate()) {

            // Eager load particiapnts
            $conversation->loadMissing('participants.participantable');
            $deletedByBothParticipants = true;

            foreach ($conversation->participants as $participant) {
                $deletedByBothParticipants = $deletedByBothParticipants &&
                    $this->actions()
                        ->whereActor($participant->participantable)
                        ->where('type', Actions::DELETE)
                        ->exists();
            }

            if ($deletedByBothParticipants) {
                $this->forceDelete();
            }
        }
    }

    /**
     * Deleting message for everyone   */
    public function deleteForEveryone(Model $user)
    {

        $conversation = $this->conversation;
        $participant = $conversation->participant($user);
        $message = $this;

        // Make sure auth belongs to conversation for this message
        abort_unless($user->belongsToConversation($conversation), 403, 'You do not belong to this conversation');

        // make sure user owns message OR allow if is admin in group
        abort_unless($message->ownedBy($user) || ($participant->isAdmin() && $message->conversation->isGroup()), 403, 'You do not have permission to delete this message');

        // if message has reply then only-soft delete it
        if ($message->hasReply()) {
            $message->delete();
        } else {

            $message->forceDelete();
        }

    }
}
