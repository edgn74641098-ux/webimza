<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Log Detayi" subtitle="Payload ve teknik detaylar.">
            <x-slot name="actions">
                <a href="{{ route('admin.logs.index') }}"><x-ui.button variant="secondary" type="button">Listeye Don</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <x-ui.app-shell>
        <x-ui.card>
            <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                <div><span class="text-slate-500">Zaman:</span> <strong>{{ optional($log->created_at)?->format('Y-m-d H:i:s') ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Kullanici:</span> <strong>{{ $log->user?->email ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Event:</span> <strong>{{ $log->event_type ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Status:</span> <strong>{{ $log->status ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Device:</span> <strong>{{ $log->device_id ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Mesaj:</span> <strong>{{ $log->message ?: '-' }}</strong></div>
                <div><span class="text-slate-500">Request ID:</span> <strong>{{ $requestId }}</strong></div>
                <div><span class="text-slate-500">Error code:</span> <strong>{{ $errorCode }}</strong></div>
            </div>
        </x-ui.card>

        <x-ui.card title="Payload JSON">
            <div class="mb-3">
                <x-ui.copy-button target="#log-payload">Copy JSON</x-ui.copy-button>
            </div>
            <x-ui.code-block id="log-payload">{{ $prettyPayload }}</x-ui.code-block>
        </x-ui.card>

        <x-ui.card title="Device bilgisi">
            @if($log->device)
                <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                    <div><span class="text-slate-500">Client type:</span> <strong>{{ $log->device->client_type ?? '-' }}</strong></div>
                    <div><span class="text-slate-500">Platform:</span> <strong>{{ $log->device->platform ?? '-' }}</strong></div>
                    <div><span class="text-slate-500">Office version:</span> <strong>{{ $log->device->office_version ?? '-' }}</strong></div>
                    <div><span class="text-slate-500">Add-in version:</span> <strong>{{ $log->device->addin_version ?? '-' }}</strong></div>
                </div>
            @else
                <x-ui.empty-state title="Device bilgisi yok" description="Bu log kaydina bagli cihaz kaydi bulunamadi." />
            @endif
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
