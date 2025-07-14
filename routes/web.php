<?php

use App\Http\Controllers\CustomLivewirePreviewFileController;
use App\Http\Controllers\CustomLiveWireUploadController;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Namu\WireChat\Facades\WireChat;
use Illuminate\Support\Facades\Schema;
use Namu\WireChat\Enums\ParticipantRole;
use Namu\WireChat\Models\Conversation;
use App\Livewire\Pages\Chat;
use App\Livewire\Pages\Chats;
use App\Http\Middleware\BelongsToConversation;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(config('wirechat.routes.middleware'))
    ->prefix(config('wirechat.routes.prefix'))
    ->group(function () {
        Route::get('/', Chats::class)->name('chats');
        Route::get('/{conversation}', Chat::class)->middleware(BelongsToConversation::class)->name('chat');
    });

Route::post('/livewire/upload-file', CustomLiveWireUploadController::class)
    ->middleware('web')
    ->name('livewire.upload-file');

Route::get('/livewire/preview-file/{filename}', CustomLivewirePreviewFileController::class)
    ->middleware('web')
    ->name('livewire.preview-file');

    

require __DIR__.'/auth.php';
