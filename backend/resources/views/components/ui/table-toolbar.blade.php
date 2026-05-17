<div {{ $attributes->merge(['class' => 'mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between']) }}>
    <div class="flex flex-1 flex-wrap items-center gap-2">{{ $filters ?? '' }}</div>
    <div class="flex items-center gap-2">{{ $actions ?? '' }}</div>
</div>
