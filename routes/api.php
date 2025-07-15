<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatApiIntegrationController;

Route::prefix('integration-api/chat')->group(function () {

    Route::get('/recent-messages-for-notifications/{user}', [ChatApiIntegrationController::class, 'getRecentMessagesForNotifications'])->name('integration.chat.recentMessagesForNotifications');

    Route::post('/{conversation}/mark-as-read/{user}', [ChatApiIntegrationController::class, 'markConversationAsRead'])->name('integration.chat.markAsRead');

    Route::get('/total-unread-messages/{user}', [ChatApiIntegrationController::class, 'getTotalUnreadMessagesCount'])->name('integration.chat.totalUnreadMessagesCount');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');