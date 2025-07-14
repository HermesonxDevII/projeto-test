<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inicio') }}
        </h2>
    </x-slot>

    <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8">
       <div class="h-[calc(100vh_-_12rem)] overflow-hidden">
            <livewire:wirechat/>
       </div>
    </div>
</x-app-layout>
