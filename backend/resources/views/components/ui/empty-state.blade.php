@props(['title' => 'Kayit Bulunamadi', 'description' => 'Bu kriterlere uygun sonuc yok.'])
<div class="ui-empty">
    <p class="font-medium text-slate-700">{{ $title }}</p>
    <p class="mt-1 text-slate-500">{{ $description }}</p>
    @if(isset($action))<div class="mt-3">{{ $action }}</div>@endif
</div>
