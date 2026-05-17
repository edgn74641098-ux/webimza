<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Loglar" subtitle="Add-in olaylarini ve hatalari inceleyin." />
    </x-slot>

    <x-ui.app-shell>
        <x-ui.card>
            <form method="GET" action="{{ route('admin.logs.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-5">
                <x-ui.select name="event_type">
                    <option value="">Event type</option>
                    @foreach($eventTypes as $eventType)
                        <option value="{{ $eventType }}" @selected(($filters['event_type'] ?? '') == $eventType)>{{ $eventType }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="status">
                    <option value="">Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') == $status)>{{ $status }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select name="user_id">
                    <option value="">Kullanici</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string)($filters['user_id'] ?? '') === (string)$user->id)>{{ $user->email }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input type="date" name="date" :value="$filters['date'] ?? ''" />
                <div class="flex gap-2">
                    <x-ui.button type="submit">Filtrele</x-ui.button>
                    <a href="{{ route('admin.logs.index') }}"><x-ui.button type="button" variant="secondary">Temizle</x-ui.button></a>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card>
            @if($logs->isEmpty())
                <x-ui.empty-state title="Henüz add-in aktivitesi yok." description="Eklenti dagitildiktan sonra loglar burada gorunecek." />
            @else
                <x-ui.table>
                    <x-slot name="head">
                        <tr>
                            <th>Zaman</th>
                            <th>Kullanici</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th>Mesaj</th>
                            <th>Device</th>
                            <th>Aksiyon</th>
                        </tr>
                    </x-slot>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ optional($log->created_at)?->format('Y-m-d H:i:s') ?? '-' }}</td>
                            <td>{{ $log->user?->email ?? '-' }}</td>
                            <td>{{ $log->event_type ?? '-' }}</td>
                            <td><x-ui.badge status="default">{{ $log->status ?? '-' }}</x-ui.badge></td>
                            <td>{{ $log->message ?: '-' }}</td>
                            <td>{{ $log->device_id ?? '-' }}</td>
                            <td><a href="{{ route('admin.logs.show', $log) }}"><x-ui.button type="button" variant="secondary">Detay</x-ui.button></a></td>
                        </tr>
                    @endforeach
                    <x-slot name="footer">{{ $logs->links() }}</x-slot>
                </x-ui.table>
            @endif
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
