@props(['type' => 'info'])
@php
$styles = [
  'info' => 'border-sky-200 bg-sky-50 text-sky-700',
  'success' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
  'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
  'error' => 'border-rose-200 bg-rose-50 text-rose-700',
];
@endphp
<div {{ $attributes->merge(['class' => 'rounded-lg border px-4 py-3 text-sm '.($styles[$type] ?? $styles['info'])]) }}>{{ $slot }}</div>
