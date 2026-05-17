@props(['label', 'value', 'hint' => null])
<div class="ui-kpi">
    <p class="ui-kpi-label">{{ $label }}</p>
    <p class="ui-kpi-value">{{ $value }}</p>
    @if($hint)
        <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
