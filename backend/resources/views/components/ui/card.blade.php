@props(['title' => null, 'subtitle' => null])
<div {{ $attributes->merge(['class' => 'ui-card']) }}>
    <div class="ui-card-body">
        @if($title || $subtitle || isset($actions))
            <div class="mb-3 flex items-start justify-between gap-3">
                <div>
                    @if($title)<h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>@endif
                    @if($subtitle)<p class="ui-subtitle">{{ $subtitle }}</p>@endif
                </div>
                @if(isset($actions))<div>{{ $actions }}</div>@endif
            </div>
        @endif
        {{ $slot }}
    </div>
</div>

