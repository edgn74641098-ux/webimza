@props(['title' => null, 'subtitle' => null])
<x-ui.card :title="$title" :subtitle="$subtitle" {{ $attributes }}>
    @isset($actions)
        <x-slot name="actions">{{ $actions }}</x-slot>
    @endisset
    {{ $slot }}
</x-ui.card>
