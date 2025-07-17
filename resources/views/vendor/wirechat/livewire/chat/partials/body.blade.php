<main
    {{--
        Define um componente reativo no Alpine.js. Inicia as variáveis de estado (`height`, `previousHeight`)
        e o método `updateScrollPosition`. Este método calcula a diferença de altura depois que novas mensagens
        são carregadas no topo e ajusta a posição da barra de rolagem para baixo, mantendo a visão do usuário no
        mesmo ponto de antes.
    --}}
    x-data="{
        height: 0,
        previousHeight: 0,
        updateScrollPosition: function() {
            newHeight = $el.scrollHeight;
            heightDifference = newHeight - height;
            $el.scrollTop += heightDifference;
            height = newHeight;
        }
    }"

    {{--
        Executa uma vez na inicialização do componente (`x-init`). Após uma pausa de 300ms (para garantir que o conteúdo
        foi renderizado), ele rola a janela de chat até o final, fazendo com que o usuário veja a mensagem mais recente
        primeiro. A rolagem é otimizada com `requestAnimationFrame`.
    --}}
    x-init="
        setTimeout(() => {
            requestAnimationFrame(() => {
                this.height = $el.scrollHeight;
                $el.scrollTop = this.height;
            });
        }, 300);
    "

    {{--
        Monitora o evento de rolagem (`scroll`). Se o usuário rolar até o topo do contêiner (`scrollTop <= 0`) e houver
        mais mensagens para carregar (verificado via Livewire com `$wire.canLoadMore`), ele aciona a função `loadMore()`
        do Livewire para buscar o histórico de mensagens mais antigas.
    --}}
    @scroll="
        scrollTop= $el.scrollTop;
        if((scrollTop<=0) && $wire.canLoadMore){
            $wire.loadMore();
        }
    "

    {{--
        Ouve um evento personalizado 'update-height' na janela do navegador (window). Ao receber o evento, executa a função
        'updateScrollPosition' para ajustar a posição da rolagem de forma suave (usando 'requestAnimationFrame'), o que impede
        o usuário de "saltar" na tela quando novas mensagens são carregadas no topo do chat.
    --}}
    @update-height.window="
        requestAnimationFrame(() => { updateScrollPosition(); });
    "

    {{--
        Ouve por um evento global 'scroll-bottom'. Quando este evento é disparado (provavelmente ao enviar uma nova mensagem),
        ele força a rolagem da tela para o final. A barra de rolagem é momentaneamente ocultada e reexibida para evitar qualquer
        "salto" visual, garantindo uma transição suave.
    --}}
    @scroll-bottom.window="
        requestAnimationFrame(() => {
            $el.style.overflowY='hidden';
            $el.scrollTop = $el.scrollHeight;
            $el.style.overflowY='auto';
        });
    "

    {{--
        Garante que o elemento permaneça oculto até que o Alpine.js seja totalmente inicializado. Isso previne o "flash de
        conteúdo não estilizado" (FOUC), que é quando o usuário vê o HTML bruto por um instante antes do JavaScript ser aplicado.
    --}}
    x-cloak

    {{-- Estilos TailwindCSS --}}
    class='flex flex-col h-full relative gap-2 gap-y-4 p-4 md:p-5 lg:p-8 grow overscroll-contain overflow-x-hidden w-full my-auto'
    
    {{-- Estilos CSS Padrão --}}
    style="contain: content"
>
    {{-- Comentar mais depois --}}
    <div
        x-cloak
        wire:loading.delay.class.remove="invisible"
        wire:target="loadMore"
        class="invisible transition-all duration-300"
    >
        <x-wirechat::loading-spin />
    </div>

    @php
        $previousMessage = null;
    @endphp

    @if ($loadedMessages)
        @foreach ($loadedMessages as $date => $messageGroup)
            <div class="sticky top-0 uppercase p-2 shadow-xs px-2.5 z-50 rounded-xl border border-gray-100/50 text-sm flex text-center justify-center bg-gray-50 w-28 mx-auto ">
                {{ $date }}
            </div>

            @foreach ($messageGroup as $key => $message)
                @php
                    $belongsToAuth = $message->belongsToAuth();
                    $parent = $message->parent ?? null;
                    $attachment = $message->attachment ?? null;
                    $isEmoji = mb_ereg('^(?:\X(?=\p{Emoji}))*\X$', $message->body ?? '');

                    if ($key > 0) {
                        $previousMessage = $messageGroup->get($key - 1);
                    }

                    $nextMessage = $key < $messageGroup->count() - 1 ? $messageGroup->get($key + 1) : null;
                @endphp

                <div class="flex gap-2" wire:key="message-{{ $key }}">
                    @if (!$belongsToAuth && !$isPrivate)
                        <div @class([
                            'shrink-0 mb-auto -mb-2',
                            'invisible' => $previousMessage && $message?->sendable?->is($previousMessage?->sendable),
                        ])>
                            <x-wirechat::avatar src="{{ $message->sendable?->cover_url ?? null }}" class="h-8 w-8" />
                        </div>
                    @endif

                    <div class="w-[95%] mx-auto">
                        <div @class([
                            'max-w-[85%] md:max-w-[78%] flex flex-col gap-y-2',
                            'ml-auto' => $belongsToAuth
                        ])>
                            {{-- Caixa de Mensagem do Usuário --}}
                            <div class="flex gap-1 md:gap-4 group transition-transform {{ $belongsToAuth ? 'justify-end' : '' }}">
                                <div class="flex flex-col gap-2 max-w-[95%] relative">
                                    <div class="bg-gray-100 text-black rounded-xl px-3 py-2 max-w-fit break-words">
                                        @if (!$belongsToAuth && $isGroup && !($previousMessage && $message?->sendable?->is($previousMessage?->sendable)))
                                            <div class="font-medium text-sm mb-1 text-purple-500">
                                                {{ $message->sendable?->display_name }}
                                            </div>
                                        @endif

                                        @if ($isEmoji)
                                            <p class="text-5xl leading-none">
                                                {{ $message->body }}
                                            </p>
                                        @elseif ($attachment)
                                            @if (str()->startsWith($attachment->mime_type, 'application/'))
                                                @include('wirechat::livewire.chat.partials.file', [
                                                    'attachment' => $attachment
                                                ])
                                            @elseif (str()->startsWith($attachment->mime_type, 'video/'))
                                                <x-wirechat::video
                                                    height="max-h-[400px]"
                                                    :cover="false"
                                                    source="{{ $attachment?->url }}"
                                                />
                                            @elseif (str()->startsWith($attachment->mime_type, 'image/'))
                                                @include('wirechat::livewire.chat.partials.image', [
                                                    'previousMessage' => $previousMessage,
                                                    'message' => $message,
                                                    'nextMessage' => $nextMessage,
                                                    'belongsToAuth' => $belongsToAuth,
                                                    'attachment' => $attachment
                                                ])
                                            @endif
                                        @elseif ($message->body)
                                            {{ $message->body }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach
    @endif
</main>