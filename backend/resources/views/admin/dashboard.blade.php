<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Dashboard" subtitle="Kritik metrikler ve son operasyon hareketleri">
            <x-slot name="actions">
                <a href="{{ route('admin.logs.index') }}"><x-ui.button variant="secondary" type="button">Loglari Goruntule</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <x-ui.app-shell>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <x-ui.stat-card label="Toplam Kullanici" :value="$kpis['total_users']" />
            <x-ui.stat-card label="Aktif Cihaz" :value="$kpis['active_devices']" />
            <x-ui.stat-card label="Guncel Imza Kullanan" :value="$kpis['up_to_date_users']" :hint="$latestTemplateVersion ? 'Versiyon '.$latestTemplateVersion : 'Aktif versiyon yok'" />
            <x-ui.stat-card label="Guncelleme Bekleyen" :value="$kpis['pending_update_users']" />
            <x-ui.stat-card label="Son 24 Saat Hata" :value="$kpis['errors_24h']" />
            <x-ui.stat-card label="Desteklenmeyen Istemci" :value="$kpis['unsupported_clients']" />
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <x-ui.card title="Son 10 Add-in Aktivitesi" subtitle="En son kaydedilen olaylar">
                @if($recentActivities->isEmpty())
                    <x-ui.empty-state title="Henuz add-in aktivitesi yok." description="Eklenti dagitildiktan sonra aktiviteler burada gorunecek." />
                @else
                    <x-ui.table>
                        <x-slot name="head"><tr><th>Zaman</th><th>Kullanici</th><th>Event</th><th>Durum</th><th>Mesaj</th></tr></x-slot>
                        @foreach($recentActivities as $activity)
                            <tr>
                                <td>{{ optional($activity->created_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ $activity->user?->email ?? $activity->email ?? '-' }}</td>
                                <td>{{ $activity->event_type ?? '-' }}</td>
                                <td><x-ui.badge :type="$activity->status === 'error' ? 'danger' : 'success'">{{ $activity->status ?? 'info' }}</x-ui.badge></td>
                                <td>{{ $activity->message ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </x-ui.card>

            <x-ui.card title="Son 10 Hata" subtitle="Hata kayitlari">
                @if($recentErrors->isEmpty())
                    <x-ui.empty-state title="Son hata yok" description="Son 24 saatte veya kayitli veride hata gorunmuyor." />
                @else
                    <x-ui.table>
                        <x-slot name="head"><tr><th>Zaman</th><th>Kullanici</th><th>Event</th><th>Mesaj</th></tr></x-slot>
                        @foreach($recentErrors as $error)
                            <tr>
                                <td>{{ optional($error->created_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ $error->user?->email ?? $error->email ?? '-' }}</td>
                                <td>{{ $error->event_type ?? '-' }}</td>
                                <td>{{ $error->message ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </x-ui.card>
        </div>

        <x-ui.card title="Hizli Aksiyonlar" subtitle="Sik kullanilan yonetim islemleri">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-4">
                <a href="{{ route('admin.templates.create') }}"><x-ui.button class="w-full">Yeni Sablon Olustur</x-ui.button></a>
                <a href="{{ route('admin.updates.index') }}"><x-ui.button variant="secondary" class="w-full" type="button">Guncelleme Baslat</x-ui.button></a>
                <form method="POST" action="{{ route('admin.deployment.build') }}" class="w-full">@csrf <x-ui.button variant="secondary" class="w-full">Deployment Manifest Olustur</x-ui.button></form>
                <a href="{{ route('admin.logs.index') }}"><x-ui.button variant="secondary" class="w-full" type="button">Loglari Goruntule</x-ui.button></a>
            </div>
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
