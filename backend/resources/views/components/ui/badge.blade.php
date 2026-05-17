@props(['status' => 'default'])
@php
$map = [
  'success' => 'ui-badge-success',
  'active' => 'ui-badge-success',
  'completed' => 'ui-badge-success',
  'error' => 'bg-rose-100 text-rose-700',
  'failed' => 'bg-rose-100 text-rose-700',
  'cancelled' => 'bg-rose-100 text-rose-700',
  'warning' => 'bg-amber-100 text-amber-700',
  'pending' => 'bg-amber-100 text-amber-700',
  'partially_completed' => 'bg-amber-100 text-amber-700',
  'expired' => 'bg-slate-200 text-slate-700',
  'info' => 'bg-sky-100 text-sky-700',
  'default' => 'ui-badge-muted',
  'inactive' => 'ui-badge-muted',
];
$key = strtolower((string) $status);
$cls = $map[$key] ?? $map['default'];
@endphp
<span {{ $attributes->merge(['class' => "ui-badge {$cls}"]) }}>{{ $slot->isEmpty() ? $status : $slot }}</span>

