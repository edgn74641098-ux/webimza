<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Add-in Cihazlari" subtitle="Hangi kullanicida eklenti calisiyor ve surum durumu nedir gorun." />
    </x-slot>

    <x-ui.app-shell>
        <x-ui.card>
            <form method="GET" action="{{ route('admin.devices.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-5">
                <x-ui.select name="client_type">
                    <option value="">Client type</option>
                    @foreach($clientTypes as $clientType)
                        <option value="{{ $clientType }}" @selected(($filters['client_type'] ?? '') == $clientType)>{{ $clientType ?: '-' }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="status">
                    <option value="">Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') == $status)>{{ $status }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="addin_version">
                    <option value="">Add-in version</option>
                    @foreach($addinVersions as $version)
                        <option value="{{ $version }}" @selected(($filters['addin_version'] ?? '') == $version)>{{ $version }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input type="date" name="last_seen_from" :value="$filters['last_seen_from'] ?? ''" />
                <div class="flex gap-2">
                    <x-ui.button type="submit">Filtrele</x-ui.button>
                    <a href="{{ route('admin.devices.index') }}"><x-ui.button type="button" variant="secondary">Temizle</x-ui.button></a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            @if($devices->isEmpty())
                <x-ui.empty-state title="Henüz cihaz kaydý yok" description="Add-in calismaya basladiktan sonra cihazlar burada gorunur." />
            @else
                <x-ui.table>
                    <x-slot name="head">
                        <tr>
                            <th>Kullanici</th>
                            <th>Email</th>
                            <th>Client type</th>
                            <th>Platform</th>
                            <th>Office version</th>
                            <th>Add-in version</th>
                            <th>Last seen</th>
                            <th>Last signature version</th>
                            <th>Status</th>
                            <th>Aksiyon</th>
                        </tr>
                    </x-slot>
                    @foreach($devices as $device)
                        <tr>
                            <td>{{ $device->display_name ?: ($device->user?->name ?? '-') }}</td>
                            <td>{{ $device->email ?: ($device->user?->email ?? '-') }}</td>
                            <td>{{ $device->client_type ?? '-' }}</td>
                            <td>{{ $device->platform ?? '-' }}</td>
                            <td>{{ $device->office_version ?? '-' }}</td>
                            <td>{{ $device->addin_version ?? '-' }}</td>
                            <td>{{ optional($device->last_seen_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ $device->last_signature_version ?? '-' }}</td>
                            <td><x-ui.badge status="default">{{ $device->status ?? '-' }}</x-ui.badge></td>
                            <td><a href="{{ route('admin.devices.show', $device) }}"><x-ui.button type="button" variant="secondary">Detay</x-ui.button></a></td>
                        </tr>
                    @endforeach
                    <x-slot name="footer">{{ $devices->links() }}</x-slot>
                </x-ui.table>
            @endif
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
