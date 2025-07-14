<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Namu\WireChat\Helpers\MorphClassResolver;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, int $conversationId) {

    $participant = session('student_id') ? \App\Models\Student::find(session('student_id')) : $user;

    $conversation = Conversation::find($conversationId);
    
    if ($conversation && $participant) {
        // code...
        $result = $participant->belongsToConversation($conversation);

        if ($result) {
            return true; // Allow access to the channel
        }
    }

    return false; // Deny access to the channel

},
    [
        'guards' => config('wirechat.routes.guards', ['web']),
        'middleware' => config('wirechat.routes.middleware', ['web', 'auth']),
    ]
);

Broadcast::channel('participant.{encodedType}.{id}', function ($user, $encodedType, $id) {
    // Decode the encoded type to get the raw value.
    $morphType = MorphClassResolver::decode($encodedType);

    if ($morphType === \App\Models\User::class) {
        return $user instanceof \App\Models\User && $user->id == $id;
    }

    if ($morphType === \App\Models\Student::class && session('student_id')) {
        $student = \App\Models\Student::find(session('student_id'));
        return $student && $student->id == $id;
    }
    
    return false;
    
}, [
    'guards' => config('wirechat.routes.guards', ['web']),
    'middleware' => config('wirechat.routes.middleware', ['web', 'auth']),
]);