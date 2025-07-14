<?php

namespace App\Http\Middleware;

use App\Models\Conversation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BelongsToConversation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = getAuthenticatedParticipant();
        $conversationId = $request->route('conversation');

        $conversation = Conversation::findOrFail($conversationId);
        
        if (! $user || ! $user->belongsToConversation($conversation)
        ) {
            abort(403, 'Forbidden');
        }

        return $next($request);

    }
}
