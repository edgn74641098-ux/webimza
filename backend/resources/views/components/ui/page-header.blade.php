@props(['title', 'subtitle' => null])
<div class="ui-page-head">
    <div>
        <h2 class="app-title">{{ $title }}</h2>
        @if($subtitle)
            <p class="ui-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="flex items-center gap-2">{{ $actions }}</div>
    @endif
</div>
