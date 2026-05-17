@props(['side' => 'right'])
<div x-show="drawer" class="fixed inset-0 z-50 flex" style="display:none;">
  <div class="absolute inset-0 bg-slate-900/40" @click="drawer=false"></div>
  <div class="relative {{ $side === 'left' ? 'mr-auto' : 'ml-auto' }} h-full w-full max-w-xl bg-white p-5 shadow-2xl">{{ $slot }}</div>
</div>
