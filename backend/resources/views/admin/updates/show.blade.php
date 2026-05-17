<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Guncelleme Detayi" subtitle="Force update kaydinin ozet durumu.">
            <x-slot name="actions">
                <a href="{{ route('admin.updates.index') }}"><x-ui.button variant="secondary" type="button">Listeye Don</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <x-ui.app-shell>
        <x-ui.card>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div><span class="text-slate-500">Kapsam:</span> <strong>{{ $scopeLabel }}</strong></div>
                <div><span class="text-slate-500">Hedef:</span> <strong>{{ $targetLabel }}</strong></div>
                <div><span class="text-slate-500">Durum:</span> <x-ui.badge status="default">{{ $update->status }}</x-ui.badge></div>
                <div><span class="text-slate-500">TTL:</span> <strong>{{ $update->ttl_days }} gun</strong></div>
                <div><span class="text-slate-500">Baslangic:</span> <strong>{{ optional($update->created_at)?->format('Y-m-d H:i') ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Expire:</span> <strong>{{ optional($update->expires_at)?->format('Y-m-d H:i') ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Etkilenen:</span> <strong>{{ $affectedUsers }}</strong></div>
                <div><span class="text-slate-500">Tamamlanan/Bekleyen:</span> <strong>{{ $completedUsers }} / {{ $pendingUsers }}</strong></div>
            </div>
            <div class="mt-4 h-2 w-full rounded bg-slate-200">
                <div class="h-2 rounded bg-blue-700" style="width: {{ $progress }}%"></div>
            </div>
            <p class="mt-4 text-sm text-slate-700"><span class="text-slate-500">Neden:</span> {{ $update->reason }}</p>
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
