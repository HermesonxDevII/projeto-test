<div class="relative w-full h-full">
  <div class="w-full flex min-h-full h-full rounded-lg" >
      <div class=" hidden md:grid bg-inherit border-r  relative w-full h-full md:w-[360px] lg:w-[400px] xl:w-[500px]  shrink-0 overflow-y-auto  ">
        <livewire:wirechat.chats/> 
      </div>
      
      <main  class="  grid  w-full  grow  h-full min-h-min relative overflow-y-auto"  style="contain:content">
        <livewire:wirechat.chat  conversation="{{$this->conversation->id}}"/>
      </main>

  </div>
  <div 
      x-data="{ open: false, image: '' }"
      x-show="open"
      x-cloak
      @open-image.window="
          image = $event.detail.src;
          open = true;
          document.body.style.overflow = 'hidden';
      "
      @keydown.escape.window="
          open = false;
          document.body.style.overflow = '';
      "
      @click.self="
          open = false;
          document.body.style.overflow = '';
      "
      class="fixed inset-0 z-[9999] bg-black/80 flex items-center justify-center p-4"
      style="display: none;"
  >
      <img 
          :src="image"
          alt="Imagem ampliada"
          class="max-w-[90vw] max-h-[90vh] rounded-xl shadow-2xl object-contain"
      />
  </div>
</div>