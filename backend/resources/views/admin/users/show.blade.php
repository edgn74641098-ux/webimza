<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Kullanici Detayi" subtitle="Kullanicinin profil, imza, cihaz ve log gorunumleri.">
            <x-slot name="actions">
                <a href="{{ route('admin.updates.index', ['scope' => 'user', 'user_id' => $user->id]) }}"><x-ui.button type="button">Guncelleme Baslat</x-ui.button></a>
                <a href="{{ route('admin.users.edit', $user) }}"><x-ui.button variant="secondary" type="button">Duzenle</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <x-ui.app-shell x-data="{ tab: 'general' }">
        <x-ui.card>
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-4">
                <x-ui.button type="button" variant="secondary" @click="tab='general'" :class="tab==='general' ? '!border-blue-700 !text-blue-700 !bg-blue-50' : ''">1. Genel Bilgiler</x-ui.button>
                <x-ui.button type="button" variant="secondary" @click="tab='preview'" :class="tab==='preview' ? '!border-blue-700 !text-blue-700 !bg-blue-50' : ''">2. Imza Onizleme</x-ui.button>
                <x-ui.button type="button" variant="secondary" @click="tab='devices'" :class="tab==='devices' ? '!border-blue-700 !text-blue-700 !bg-blue-50' : ''">3. Cihazlar</x-ui.button>
                <x-ui.button type="button" variant="secondary" @click="tab='logs'" :class="tab==='logs' ? '!border-blue-700 !text-blue-700 !bg-blue-50' : ''">4. Loglar</x-ui.button>
            </div>

            <div x-show="tab==='general'" class="pt-5">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3"><p class="text-xs text-slate-500">Kullanici</p><p class="text-sm font-semibold text-slate-900">{{ $user->name ?: '-' }}</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3"><p class="text-xs text-slate-500">E-posta</p><p class="text-sm font-semibold text-slate-900">{{ $user->email ?: '-' }}</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3"><p class="text-xs text-slate-500">Departman</p><p class="text-sm font-semibold text-slate-900">{{ $user->department?->name ?? '-' }}</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3"><p class="text-xs text-slate-500">Unvan</p><p class="text-sm font-semibold text-slate-900">{{ $user->title ?? '-' }}</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3"><p class="text-xs text-slate-500">Telefon</p><p class="text-sm font-semibold text-slate-900">{{ $user->phone ?? '-' }}</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3"><p class="text-xs text-slate-500">Mobil</p><p class="text-sm font-semibold text-slate-900">{{ $user->mobile ?? '-' }}</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3"><p class="text-xs text-slate-500">Sirket</p><p class="text-sm font-semibold text-slate-900">{{ $user->company ?? '-' }}</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3"><p class="text-xs text-slate-500">Son gorulme</p><p class="text-sm font-semibold text-slate-900">{{ optional($user->last_seen_at)?->format('Y-m-d H:i') ?? '-' }}</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3"><p class="text-xs text-slate-500">Durum</p><p class="text-sm font-semibold text-slate-900"><x-ui.badge :status="$user->is_active ? 'active' : 'inactive'">{{ $user->is_active ? 'Aktif' : 'Pasif' }}</x-ui.badge></p></div>
                </div>
            </div>

            <div x-show="tab==='preview'" x-cloak class="pt-5">
                @if(!$resolvedTemplate)
                    <x-ui.empty-state title="No template assigned" description="Bu kullanici icin uygulanacak aktif imza sablonu bulunamadi." />
                @else
                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        <x-ui.card title="Imza Cozum Bilgisi">
                            <div class="space-y-2 text-sm">
                                <div><span class="text-slate-500">Kullanilacak sablon:</span> <strong class="text-slate-900">{{ $resolvedTemplate->name }}</strong></div>
                                <div><span class="text-slate-500">Kural kaynagi:</span> <x-ui.badge status="default">{{ $resolvedFrom ?? '-' }}</x-ui.badge></div>
                                <div><span class="text-slate-500">Template version:</span> <strong class="text-slate-900">{{ $resolvedTemplate->version ?? '-' }}</strong></div>
                            </div>
                        </x-ui.card>

                        <x-ui.card title="Render Edilmis HTML Imza">
                            <iframe class="h-[260px] w-full rounded-lg border border-slate-200 bg-white" srcdoc="{{ e($preview['html'] ?? '') }}"></iframe>
                        </x-ui.card>
                    </div>

                    <x-ui.card title="Plain Text Imza" class="mt-4">
                        <x-ui.code-block>{{ $preview['text'] ?? '-' }}</x-ui.code-block>
                    </x-ui.card>
                @endif
            </div>

            <div x-show="tab==='devices'" x-cloak class="pt-5">
                @if($user->addinDevices->isEmpty())
                    <x-ui.empty-state title="Cihaz yok" description="Bu kullaniciya ait add-in cihazi kaydi bulunmuyor." />
                @else
                    <x-ui.table>
                        <x-slot name="head"><tr><th>Device ID</th><th>Client</th><th>Platform</th><th>Office</th><th>Add-in</th><th>Last Seen</th><th>Last Signature</th><th>Status</th></tr></x-slot>
                        @foreach($user->addinDevices as $device)
                            <tr>
                                <td>{{ $device->device_id }}</td>
                                <td>{{ $device->client_type ?? '-' }}</td>
                                <td>{{ $device->platform ?? '-' }}</td>
                                <td>{{ $device->office_version ?? '-' }}</td>
                                <td>{{ $device->addin_version ?? '-' }}</td>
                                <td>{{ optional($device->last_seen_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ $device->last_signature_version ?? '-' }}</td>
                                <td><x-ui.badge status="default">{{ $device->status ?? '-' }}</x-ui.badge></td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </div>

            <div x-show="tab==='logs'" x-cloak class="pt-5">
                @if($recentLogs->isEmpty())
                    <x-ui.empty-state title="Log yok" description="Bu kullaniciya ait son 50 log kaydi bulunmuyor." />
                @else
                    <x-ui.table>
                        <x-slot name="head"><tr><th>Zaman</th><th>Event</th><th>Durum</th><th>Mesaj</th><th>Device</th></tr></x-slot>
                        @foreach($recentLogs as $log)
                            <tr>
                                <td>{{ optional($log->created_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ $log->event_type ?? '-' }}</td>
                                <td><x-ui.badge :status="$log->status === 'error' ? 'danger' : 'success'">{{ $log->status ?? 'info' }}</x-ui.badge></td>
                                <td>{{ $log->message ?: '-' }}</td>
                                <td>{{ $log->device_id ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </div>
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
