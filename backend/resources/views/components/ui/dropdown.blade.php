<div x-data="{ open:false }" class="relative">
  <div @click="open=!open">{{ $trigger }}</div>
  <div x-show="open" @click.outside="open=false" class="absolute right-0 z-30 mt-2 w-44 rounded-lg border border-slate-200 bg-white p-1 shadow">{{ $slot }}</div>
</div>
