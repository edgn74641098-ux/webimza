@props(['text' => '', 'target' => null])
@php
$script = $target
    ? "navigator.clipboard.writeText((document.querySelector('{$target}')?.textContent)||'')"
    : 'navigator.clipboard.writeText('.json_encode($text).')';
@endphp
<button type="button" class="ui-btn-secondary" onclick="{{ $script }}">
    {{ $slot->isEmpty() ? 'Kopyala' : $slot }}
</button>
