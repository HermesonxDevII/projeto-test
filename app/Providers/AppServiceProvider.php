<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Livewire\Pages\Chats as Index;
use App\Livewire\New\Chat as NewChat;
use App\Livewire\New\Group as NewGroup;
use App\Livewire\Pages\Chat as View;
use App\Livewire\Chats\Chats;
use App\Livewire\Modals\Modal;
use App\Livewire\Chat\Chat;
use App\Livewire\Chat\Info;
use App\Livewire\Chat\Group\Info as GroupInfo;
use App\Livewire\Chat\Drawer;
use App\Livewire\Chat\Group\AddMembers;
use App\Livewire\Chat\Group\Members;
use App\Livewire\Chat\Group\Permissions;
use App\Livewire\Widgets\WireChat;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Pages
        Livewire::component('wirechat.pages.index', Index::class);
        Livewire::component('wirechat.pages.view', View::class);

        // Chats
        Livewire::component('wirechat.chats', Chats::class);

        // modal
        Livewire::component('wirechat.modal', Modal::class);

        Livewire::component('wirechat.new.chat', NewChat::class);
        Livewire::component('wirechat.new.group', NewGroup::class);

        // Chat/Group related components
        Livewire::component('wirechat.chat', Chat::class);
        Livewire::component('wirechat.chat.info', Info::class);
        Livewire::component('wirechat.chat.group.info', GroupInfo::class);
        Livewire::component('wirechat.chat.drawer', Drawer::class);
        Livewire::component('wirechat.chat.group.add-members', AddMembers::class);
        Livewire::component('wirechat.chat.group.members', Members::class);
        Livewire::component('wirechat.chat.group.permissions', Permissions::class);

        // stand alone widget component
        Livewire::component('wirechat', WireChat::class);
    }
}
