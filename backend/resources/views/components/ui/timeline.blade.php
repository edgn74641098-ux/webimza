@props(['items' => []])
<ol class="relative border-s border-slate-200 ps-4">
@foreach($items as $item)
  <li class="mb-4 ms-2">
    <span class="absolute -start-1 mt-1 h-2 w-2 rounded-full bg-teal-600"></span>
    <p class="text-sm font-medium text-slate-800">{{ $item['title'] ?? '-' }}</p>
    <p class="text-xs text-slate-500">{{ $item['time'] ?? '' }}</p>
    @if(!empty($item['description']))<p class="mt-1 text-sm text-slate-600">{{ $item['description'] }}</p>@endif
  </li>
@endforeach
</ol>
