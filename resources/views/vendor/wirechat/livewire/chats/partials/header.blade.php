
@use('Namu\WireChat\Facades\WireChat')

<header class="px-3 z-10 sticky top-0 w-full py-2 " dusk="header">


    {{-- Title/name and Icon --}}
    <section class=" justify-between flex items-center   pb-2">

        @if (isset($title))
            <div class="flex items-center gap-2 truncate  " wire:ignore>
                <h2 class=" text-2xl font-bold"  dusk="title">{{$title}}</h2> 
            </div>
        @endif



        <div class="flex gap-x-3 items-center  ">

            @if ($showNewChatModalButton)
            
            <x-wirechat::actions.new-chat widget="{{$this->isWidget()}}">
                <button id="open-new-chat-modal-button" class=" flex items-center focus:outline-hidden">
                    <svg class="w-8 h-8 -mb-1 text-gray-500 hover:text-gray-900"
                        xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                        <g fill="none" stroke="currentColor">
                            <path
                                d="M12.875 5C9.225 5 7.4 5 6.242 6.103a4 4 0 0 0-.139.139C5 7.4 5 9.225 5 12.875V17c0 .943 0 1.414.293 1.707S6.057 19 7 19h4.125c3.65 0 5.475 0 6.633-1.103a4 4 0 0 0 .139-.139C19 16.6 19 14.775 19 11.125" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 10h6m-6 4h3m7-6V2m-3 3h6" />
                        </g>
                    </svg>                    
                </button>
            </x-wirechat::actions.new-chat>
            @endif


            {{-- Only show if is not widget --}}
            @if ($showHomeRouteButton)
            <a id="redirect-button" href="{{ getReturnURL() }}" class="flex items-center">                

                <svg class="bi bi-x-octagon-fill w-8 my-auto h-8 stroke-[0.9] text-gray-500 transition-colors duration-300 hover:text-gray-900"
                    xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
                    <g fill="none" stroke="currentColor">
                        <path
                            d="M5 12.76c0-1.358 0-2.037.274-2.634c.275-.597.79-1.038 1.821-1.922l1-.857C9.96 5.75 10.89 4.95 12 4.95s2.041.799 3.905 2.396l1 .857c1.03.884 1.546 1.325 1.82 1.922c.275.597.275 1.276.275 2.634V17c0 1.886 0 2.828-.586 3.414S16.886 21 15 21H9c-1.886 0-2.828 0-3.414-.586S5 18.886 5 17z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.5 21v-5a1 1 0 0 0-1-1h-3a1 1 0 0 0-1 1v5" />
                    </g>
                </svg>                
            </a>
            @endif


        </div>



    </section>

    {{-- Search input --}}
    @if ($allowChatsSearch)
        <section class="mt-4">
            <div class="px-2 rounded-lg bg-gray-100  grid grid-cols-12 items-center">

                <label for="chats-search-field" class="col-span-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-5 w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </label>

                <input id="chats-search-field" name="chats_search" maxlength="100" type="search" wire:model.live.debounce='search'
                    placeholder="{{ __('wirechat::chats.inputs.search.placeholder')  }}" autocomplete="off"
                    class=" col-span-11 border-0  bg-inherit outline-hidden w-full focus:outline-hidden  focus:ring-0 hover:ring-0">
          
                </div>

        </section>
    @endif

</header>
