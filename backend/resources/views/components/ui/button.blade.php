@props(['variant' => 'primary', 'type' => 'button'])
@php
$variants = [
 'primary' => 'ui-btn-primary',
 'secondary' => 'ui-btn-secondary',
 'danger' => 'ui-btn bg-rose-600 text-white hover:bg-rose-700',
 'ghost' => 'ui-btn text-slate-700 hover:bg-slate-100',
];
@endphp
<button type="{{ $type }}" {{ $attributes->merge(['class' => $variants[$variant] ?? $variants['primary']]) }}>{{ $slot }}</button>
