@props(['empty' => null])
<x-ui.table :empty="$empty" {{ $attributes }}>
    @isset($head)<x-slot name="head">{{ $head }}</x-slot>@endisset
    {{ $slot }}
    @isset($footer)<x-slot name="footer">{{ $footer }}</x-slot>@endisset
</x-ui.table>
