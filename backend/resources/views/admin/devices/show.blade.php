<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Cihaz Detayi" subtitle="Device metadata, heartbeat ve son loglar.">
            <x-slot name="actions">
                <a href="{{ route('admin.devices.index') }}"><x-ui.button variant="secondary" type="button">Listeye Don</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <x-ui.app-shell>
        <x-ui.card title="Device metadata">
            <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                <div><span class="text-slate-500">Kullanici:</span> <strong>{{ $device->display_name ?: ($device->user?->name ?? '-') }}</strong></div>
                <div><span class="text-slate-500">Email:</span> <strong>{{ $device->email ?: ($device->user?->email ?? '-') }}</strong></div>
                <div><span class="text-slate-500">Device ID:</span> <strong>{{ $device->device_id }}</strong></div>
                <div><span class="text-slate-500">Client type:</span> <strong>{{ $device->client_type ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Platform:</span> <strong>{{ $device->platform ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Host:</span> <strong>{{ $device->host ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Office version:</span> <strong>{{ $device->office_version ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Add-in version:</span> <strong>{{ $device->addin_version ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Last seen:</span> <strong>{{ optional($device->last_seen_at)?->format('Y-m-d H:i') ?? '-' }}</strong></div>
                <div><span class="text-slate-500">Last signature version:</span> <strong>{{ $device->last_signature_version ?? '-' }}</strong></div>
            </div>
        </x-ui.card>

        <x-ui.card title="Son heartbeat">
            @if(!$lastHeartbeat)
                <x-ui.empty-state title="Heartbeat yok" description="Bu cihaza ait heartbeat olayi henuz yok." />
            @else
                <div class="space-y-2 text-sm">
                    <div><span class="text-slate-500">Zaman:</span> <strong>{{ optional($lastHeartbeat->created_at)?->format('Y-m-d H:i:s') }}</strong></div>
                    <div><span class="text-slate-500">Mesaj:</span> <strong>{{ $lastHeartbeat->message ?: '-' }}</strong></div>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card title="Compatibility bilgisi">
            <x-ui.badge :status="$compatibility === 'supported' ? 'active' : 'inactive'">{{ $compatibility }}</x-ui.badge>
        </x-ui.card>

        <x-ui.card title="Son 20 log">
            @if($logs->isEmpty())
                <x-ui.empty-state title="Log yok" description="Bu cihaza ait log kaydi bulunmuyor." />
            @else
                <x-ui.table>
                    <x-slot name="head"><tr><th>Zaman</th><th>Event</th><th>Status</th><th>Mesaj</th><th>Aksiyon</th></tr></x-slot>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ optional($log->created_at)?->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->event_type }}</td>
                            <td><x-ui.badge status="default">{{ $log->status }}</x-ui.badge></td>
                            <td>{{ $log->message ?: '-' }}</td>
                            <td><a href="{{ route('admin.logs.show', $log) }}"><x-ui.button type="button" variant="secondary">Log Detay</x-ui.button></a></td>
                        </tr>
                    @endforeach
                </x-ui.table>
            @endif
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
