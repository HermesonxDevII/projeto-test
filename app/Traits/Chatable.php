<?php

namespace App\Traits;

use App\Models\Conversation;
use App\Models\Group;
use App\Models\Message;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as CustomCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Namu\WireChat\Enums\ConversationType;
use Namu\WireChat\Enums\ParticipantRole;
use Namu\WireChat\Facades\WireChat;

/**
 * Trait Chatable
 *
 * This trait defines the behavior for models that can participate in conversations within the WireChat system.
 * It provides methods to establish relationships with conversations, define cover images for avatars,
 * and specify the route for redirecting to the user's profile page.
 */
trait Chatable
{
    use Actor;

    /**
     * Establishes a relationship between the user and conversations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function conversations()
    {
        return getAuthenticatedParticipant()->morphToMany(
            Conversation::class, // The related model
            'participantable',   // The polymorphic field (participantable_id & participantable_type)
            'wire_participants', // The participants table
            'participantable_id', // The foreign key on the participants table for the User model
            'conversation_id'     // The foreign key for the Conversation model
        )->withPivot('conversation_id'); // Optionally load conversation_id from the pivot table
    }

    /**
     * Creates a private conversation with another participant and adds participants.
     *
     * @param  Model  $participant  The participant to create a conversation with
     * @param  string|null  $message  The initial message (optional)
     * @return Conversation|null
     */
    public function createConversationWith(Model $participant, ?string $message = null)
    {

        
        // abort if is not allowed to create new chats
        abort_unless($this->canCreateChats(), 403, 'Você não tem permissão para criar conversas.');

        $participantId = $participant->id;
        $participantType = $participant->getMorphClass();

        $authenticatedUserId = $this->id;
        $authenticatedUserType = $this->getMorphClass();

        // Determine if this is a self-conversation (for the same user as both participants)
        $selfConversationCheck = $participantId == $authenticatedUserId && $participantType == $authenticatedUserType;

        //  dd($selfConversationCheck);
        $existingConversationQuery = Conversation::withoutGlobalScopes()
            ->where('type', $selfConversationCheck ? ConversationType::SELF : ConversationType::PRIVATE)
            ->whereHas('participants', function ($query) use ($authenticatedUserId, $authenticatedUserType, $participantId, $participantType, $selfConversationCheck) {
                if ($selfConversationCheck) {
                    // Self-conversation: check for one participant record
                    $query->where('participantable_id', $authenticatedUserId)
                        ->where('participantable_type', $authenticatedUserType);
                } else {
                    // Private conversation between two participants
                    $query->where(function ($query) use ($authenticatedUserId, $authenticatedUserType) {
                        $query->where('participantable_id', $authenticatedUserId)
                            ->where('participantable_type', $authenticatedUserType);
                    })->orWhere(function ($query) use ($participantId, $participantType) {
                        $query->where('participantable_id', $participantId)
                            ->where('participantable_type', $participantType);
                    });
                }
            }, '=', $selfConversationCheck ? 1 : 2);

        // Get the first matching conversation
        $existingConversation = $existingConversationQuery->first();

        // dd($existingConversation,$selfConversationCheck);

        // If an existing conversation is found, return it
        if ($existingConversation) {
            return $existingConversation;
        }

        // Create a new conversation
        $existingConversation = new Conversation;
        $existingConversation->type = $selfConversationCheck ? ConversationType::SELF : ConversationType::PRIVATE;
        $existingConversation->save();

        // Add the authenticated user as a participant
        Participant::create([
            'conversation_id' => $existingConversation->id,
            'participantable_id' => $authenticatedUserId,
            'participantable_type' => $authenticatedUserType,
            'role' => ParticipantRole::OWNER,
        ]);

        // For non-self conversations, add the other participant
        if (! $selfConversationCheck) {
            Participant::create([
                'conversation_id' => $existingConversation->id,
                'participantable_id' => $participantId,
                'participantable_type' => $participantType,
                'role' => ParticipantRole::OWNER,
            ]);
        }

        // Create an initial message if provided
        if (! empty($message)) {
            Message::create([
                'sendable_id' => $authenticatedUserId,
                'sendable_type' => $authenticatedUserType,
                'conversation_id' => $existingConversation->id,
                'body' => $message,
            ]);
        }

        return $existingConversation;
    }

    /**
     * Room configuration
     */

    /**
     * Create group
     */
    public function createGroup(string $name, ?string $description = null, ?UploadedFile $photo = null, ?int $classroom_id = null): Conversation
    {

        // abort if is not allowed to create new groups
        abort_unless($this->canCreateGroups(), 403, 'Você não tem permissão para criar grupos.');

        // create rooom
        // Otherwise, create a new conversation
        $conversation = new Conversation;
        $conversation->type = ConversationType::GROUP;
        $conversation->save();

        // create room
        $group = $conversation->group()->create([
            'name' => $name,
            'description' => $description,
            'allow_members_to_send_messages' => 0,
            'classroom_id' => $classroom_id
        ]);

        // create and save photo is present
        if ($photo) {
            // save photo to disk
            $path = $photo->store(WireChat::storageFolder(), WireChat::storageDisk());

            // create attachment
            $group->cover()->create([
                'file_path' => $path,
                'file_name' => basename($path),
                'original_name' => $photo->getClientOriginalName(),
                'mime_type' => $photo->getMimeType(),
                'url' => Storage::url($path),
            ]);
        }

        // create participant as owner
        Participant::create([
            'conversation_id' => $conversation->id,
            'participantable_id' => $this->id,
            'participantable_type' => $this->getMorphClass(),
            'role' => ParticipantRole::OWNER,
        ]);

        return $conversation;
    }

    /**
     * Exit a chat:group|channel by marking the user's participant record as exited.
     */
    public function exitConversation(Conversation $conversation): bool
    {

        // get participant
        $participant = $conversation->participant($this);

        return $participant ? $participant->exitConversation() : false;
    }

    /**
     * Creates a conversation if one doesn't already exist with the recipient model,
     * or uses an existing conversation directly, and sends the attached message.
     * Works with both private and group conversations in a polymorphic manner.
     *
     * @param  Model  $model  - The recipient model or conversation instance
     * @param  string  $message  - The message content to send
     * @return Message|null
     */
    public function sendMessageTo(Model $model, string $message)
    {
        // Check if the recipient is a model (polymorphic) and not a conversation
        if (! $model instanceof Conversation) {
            // Ensure the model has the required trait
            if (! in_array(Chatable::class, class_uses($model))) {
                abort(403, 'The provided model does not support chat functionality.');
            }
            // Create or get a private conversation with the recipient
            $conversation = $this->createConversationWith($model);
        } else {
            // If it's a Conversation, use it directly
            $conversation = $model;

            // Optionally, check that the current model is part of the conversation
            if (! $this->belongsToConversation($conversation)) {
                abort(403, 'You do not have access to this conversation.'); // Exit if not a participant
            }
        }

        // Proceed to create the message if a valid conversation is found or created
        if ($conversation) {

            $createdMessage = Message::create([
                'conversation_id' => $conversation->id,
                'sendable_type' => $this->getMorphClass(), // Polymorphic sender type
                'sendable_id' => $this->id, // Polymorphic sender ID
                'body' => $message,
            ]);

            // update auth participant last active
            $participant = $conversation->participant($this);
            $participant->update(['last_active_at' => now()]);

            // Update the conversation timestamp
            $conversation->updated_at = now();
            $conversation->save();

            return $createdMessage;
        }

        return null;
    }

    /**
     * Accessor Returns the URL for the user's cover image (used as an avatar).
     * Customize this based on your avatar field.
     */
    public function getCoverUrlAttribute(): ?string
    {
        return null;  // Adjust 'avatar_url' to your field
    }

    /**
     * Accessor Returns the URL for the user's profile page.
     * Customize this based on your routing or profile setup.
     */
    public function getProfileUrlAttribute(): ?string
    {
        return null;  // Adjust 'profile' route as needed
    }

    /**
     * Accessor Returns the display name for the user.
     * Customize this based on your display name field.
     */
    public function getDisplayNameAttribute(): ?string
    {
        return $this->full_name ?? $this->name ?? 'Usuário';  // Adjust 'name' field if needed
    }

    /**
     * Get unread messages count for the user, across all conversations or within a specific conversation.
     */
    public function getUnreadCount(?Conversation $conversation = null): int
    {
        // If a specific conversation is provided, use the conversation's getUnreadCountFor method
        if ($conversation) {
            return $conversation->getUnreadCountFor($this);
        }

        // If no conversation is provided, calculate unread messages across all user conversations
        $totalUnread = 0;

        foreach ($this->conversations as $conv) {
            $totalUnread += $conv->getUnreadCountFor($this);
        }

        return $totalUnread;
    }

    /**
     * Check if the user belongs to a conversation.
     */
    public function belongsToConversation(Conversation $conversation, bool $withoutGlobalScopes = false): bool
    {
        // Check if participants are already loaded
        if ($conversation->relationLoaded('participants')) {
            // If loaded, simply check the existing collection
            $participants = $conversation->participants;

            if ($withoutGlobalScopes) {
                $participants->withoutGlobalScopes();
            }

            return $participants->contains(function ($participant) {
                return $participant->participantable_id == $this->id &&
                    $participant->participantable_type == $this->getMorphClass();
            });
        }

        $participants = $conversation->participants();

        if ($withoutGlobalScopes) {
            $participants->withoutGlobalScopes();
        }

        // If not loaded, perform the query
        return $participants
            ->where('participantable_id', $this->id)
            ->where('participantable_type', $this->getMorphClass())
            ->exists();
    }

    /**
     * Delete a conversation
     */
    public function deleteConversation(Conversation $conversation)
    {

        // use already created methods inside conversation model
        $conversation->deleteFor($this);
    }

    public function clearConversation(Conversation $conversation)
    {

        // use already created methods inside conversation model
        $conversation->clearFor($this);
    }

    /**
     * Check if the user has a private conversation with another user.
     */
    public function hasConversationWith(Model $user): bool
    {        
        $participantId = $user->id;
        $participantType = $user->getMorphClass();

        $authenticatedUserId = $this->id;
        $authenticatedUserType = $this->getMorphClass();

        // Check if this is a self-conversation (both participants are the authenticated user)
        $selfConversationCheck = $participantId === $authenticatedUserId && $participantType === $authenticatedUserType;

        // Define the base query for finding conversations
        $existingConversationQuery = Conversation::whereIn('type', [ConversationType::PRIVATE, ConversationType::SELF]);

        // If it's a self-conversation, adjust the query to check for two identical participants
        if ($selfConversationCheck) {
            $existingConversationQuery->whereHas('participants', function ($query) use ($authenticatedUserId, $authenticatedUserType) {
                $query->select('conversation_id')
                    ->where('participantable_id', $authenticatedUserId)
                    ->where('participantable_type', $authenticatedUserType)
                    ->whereType(ConversationType::SELF)
                    ->groupBy('conversation_id')
                    ->havingRaw('COUNT(*) = 1'); // Ensuring two participants in the conversation
            });
        } else {
            // If it's a conversation between two different participants, adjust the query accordingly
            $existingConversationQuery->whereHas('participants', function ($query) use ($authenticatedUserId, $authenticatedUserType, $participantId, $participantType) {
                $query->select('conversation_id')
                    ->whereIn('participantable_id', [$authenticatedUserId, $participantId])
                    ->whereIn('participantable_type', [$authenticatedUserType, $participantType])
                    ->whereType(ConversationType::PRIVATE)
                    ->groupBy('conversation_id')
                    ->havingRaw('COUNT(*) = 2'); // Ensure both participants are different
            });
        }

        // Execute the query and get the first matching conversation
        return $existingConversationQuery->exists();
    }

    /**
     * Check if the user has deleted a conversation.
     *
     * @param  Conversation  $conversation  The conversation to check for deletion status.
     * @param  bool  $checkDeletionExpired  Optional. When true, checks if the deletion has "expired."
     *                                      Deletion is considered expired if the conversation has been updated after it was deleted by the user.
     *                                      Default is false, which checks only if the conversation has been deleted, regardless of updates.
     * @return bool True if the conversation is deleted, false otherwise.
     */
    public function hasDeletedConversation(Conversation $conversation, bool $checkDeletionExpired = false): bool
    {
        $participant = $conversation->participant($this);

        return $participant?->hasDeletedConversation($checkDeletionExpired);
    }

    public function conversationDeletionExpired(Conversation $conversation): bool
    {

        return $this->hasDeletedConversation($conversation, true);
    }

    /**
     * Search for users who are eligible to participate in a conversation.
     * This method can be customized to include additional filtering logic,
     * such as limiting results to friends, followers, or other specific groups.
     *
     * @param  string  $query  The search term to match against user fields.
     * @return Collection|null A collection of users matching the search criteria,
     *                         or null if no matches are found.
     */
    public function searchChatables(string $query): ?CustomCollection
    {
        if (blank($query)) {
            return null;
        }
        
    
        $searchableFieldsByModel = session()->has('student_id')
            ? [\App\Models\User::class]
            : [\App\Models\User::class => ['name'], \App\Models\Student::class => ['full_name']];
    
        // Models que você quer pesquisar (User e Student)
        $models = session()->has('student_id')
            ? [\App\Models\User::class] 
            : [\App\Models\User::class, \App\Models\Student::class];
    
        $results = collect();
    
        foreach ($models as $modelClass) {
            $modelInstance = app($modelClass);
            $table = $modelInstance->getTable();
    
            // Cache das colunas do model
            $columns = Schema::getColumnListing($table);
            
            $queryBuilder = $modelClass::query();

            $searchableFields = $searchableFieldsByModel[$modelClass] ?? [];
            
            $queryBuilder->where(function ($queryBuilder) use ($columns, $searchableFields, $query) {
                foreach ($searchableFields as $field) {
                    if (in_array($field, $columns)) {
                        $queryBuilder->orWhere($field, 'LIKE', '%'.$query.'%');
                    }
                }
            });
    
            // Pega até 10 de cada model
            $results = $results->merge($queryBuilder->limit(10)->get());
        }
        
        return $results;
    }

    /**
     * Retrieve the searchable fields defined in configuration
     * and check if they exist in the database table schema.
     *
     * @return array|null The array of searchable fields or null if none found.
     */
    public function getWireSearchableFields(): ?array
    {
        // Define the fields specified as searchable in the configuration
        $fieldsToCheck = config('wirechat.user_searchable_fields');

        // Get the table name associated with the model
        $tableName = $this->getTable();

        // Get the list of columns in the database table
        $tableColumns = Schema::getColumnListing($tableName);

        // Filter the fields to include only those that exist in the table schema
        $searchableFields = array_intersect($fieldsToCheck, $tableColumns);

        return $searchableFields ?: null;
    }

    /* Checking roles in conversation */

    /**
     * Check if the user is an admin in a specific conversation.
     * Or if if owner , because owner can also be admin
     */
    public function isAdminIn(Group|Conversation $entity): bool
    {

        // check if is not Conversation model
        if (! ($entity instanceof Conversation)) {

            $conversation = $entity->conversation;
        }
        // means it is group to get Parent Relationship
        else {

            $conversation = $entity;
        }

        $pariticipant = $conversation->participant($this);

        return $pariticipant->isAdmin() || $pariticipant->isOwner();
    }

    /**
     * Check if the user is the owner of a specific conversation.
     */
    public function isOwnerOf(Group|Conversation $entity): bool
    {

        // check if is not Conversation model
        if (! ($entity instanceof Conversation)) {

            $conversation = $entity->conversation;
        }
        // means it is group to get Parent Relationship
        else {

            $conversation = $entity;
        }
        // If not loaded, perform the query
        $pariticipant = $conversation->participant($this);

        return (bool) $pariticipant?->isOwner();
    }

    /**
     * Determine if the user can create new groups.
     */
    public function canCreateGroups(): bool
    {
        if(session()->has('student_id')) {
            return false;
        }

        return true;
        //return $this->hasVerifiedEmail();
    }
}
