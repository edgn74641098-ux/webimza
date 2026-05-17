@props(['open' => false])
<div x-data="{ open: {{ $open ? 'true' : 'false' }} }" x-show="open" class="fixed inset-0 z-50" style="display:none;">
  <div class="absolute inset-0 bg-slate-900/40" @click="open=false"></div>
  <div class="relative mx-auto mt-12 w-full max-w-2xl rounded-xl bg-white p-5 shadow-2xl">{{ $slot }}</div>
</div>
