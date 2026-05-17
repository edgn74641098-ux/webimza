<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Add-in Cihazlari" subtitle="Outlook istemci ve cihaz envanteri">
            <x-slot name="actions">
                <a href="{{ route('admin.updates.index') }}"><x-ui.button>Guncelleme Yonetimi</x-ui.button></a>
                <a href="{{ route('admin.logs.index') }}"><x-ui.button variant="secondary" type="button">Loglar</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>
    <x-ui.app-shell x-data="{ drawer:false, diagnostic:null }">
        <x-ui.card>
            <form class="grid grid-cols-1 gap-2 md:grid-cols-8">
                <x-ui.select name="client_type"><option value="">Client Type</option>@foreach($clientTypes as $ct)<option value="{{ $ct }}" @selected(($filters['client_type'] ?? '')==$ct)>{{ $ct }}</option>@endforeach</x-ui.select>
                <x-ui.select name="platform"><option value="">Platform</option>@foreach($platforms as $p)<option value="{{ $p }}" @selected(($filters['platform'] ?? '')==$p)>{{ $p }}</option>@endforeach</x-ui.select>
                <x-ui.select name="addin_version"><option value="">Add-in Version</option>@foreach($addinVersions as $v)<option value="{{ $v }}" @selected(($filters['addin_version'] ?? '')==$v)>{{ $v }}</option>@endforeach</x-ui.select>
                <x-ui.select name="office_version"><option value="">Office Version</option>@foreach($officeVersions as $ov)<option value="{{ $ov }}" @selected(($filters['office_version'] ?? '')==$ov)>{{ $ov }}</option>@endforeach</x-ui.select>
                <x-ui.select name="status"><option value="">Status</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected(($filters['status'] ?? '')==$s)>{{ $s }}</option>@endforeach</x-ui.select>
                <x-ui.input name="q" :value="$filters['q'] ?? ''" placeholder="Last Seen / E-posta / Device" />
                <label class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-sm"><input type="checkbox" name="unsupported_only" value="1" @checked(($filters['unsupported_only'] ?? false))> Unsupported Only</label>
                <div class="flex gap-2"><x-ui.button type="submit">Filtrele</x-ui.button><a href="{{ route('admin.devices.index') }}"><x-ui.button variant="secondary" type="button">Temizle</x-ui.button></a></div>
            </form>
        </x-ui.card>

        <x-ui.card>
            @if($devices->isEmpty())
                <x-ui.empty-state title="Device yok" description="Henuz cihaz kaydi olusmadi." />
            @else
                <x-ui.table>
                    <x-slot name="head"><tr><th>Kullanıcı</th><th>E-posta</th><th>Device ID</th><th>Client Type</th><th>Platform</th><th>Host</th><th>Office Version</th><th>Add-in Version</th><th>Last Seen</th><th>Last Check</th><th>Last Signature Version</th><th>Status</th><th>Aksiyonlar</th></tr></x-slot>
                    @foreach($devices as $d)
                        <tr>
                            <td>{{ $d->user?->name ?? '-' }}</td>
                            <td>{{ $d->email ?? '-' }}</td>
                            <td>{{ $d->device_id ?? '-' }}</td>
                            <td>{{ $d->client_type ?? '-' }}</td>
                            <td>{{ $d->platform ?? '-' }}</td>
                            <td>{{ $d->host ?? '-' }}</td>
                            <td>{{ $d->office_version ?? '-' }}</td>
                            <td>{{ $d->addin_version ?? '-' }}</td>
                            <td>{{ $d->last_seen_at ?? '-' }}</td>
                            <td>{{ $d->last_check_at ?? '-' }}</td>
                            <td>{{ $d->last_signature_version ?? '-' }}</td>
                            <td><x-ui.badge :status="$d->status ?: 'default'">{{ $d->status ?? '-' }}</x-ui.badge></td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @if($d->user_id)
                                        <a href="{{ route('admin.users.show', $d->user_id) }}"><x-ui.button variant="secondary" type="button">Detay</x-ui.button></a>
                                    @else
                                        <x-ui.button variant="secondary" type="button" disabled>Detay</x-ui.button>
                                    @endif
                                    <a href="{{ route('admin.logs.index', ['q' => $d->device_id]) }}"><x-ui.button variant="secondary" type="button">Logları Gör</x-ui.button></a>
                                    <x-ui.button variant="secondary" type="button" @click="drawer=true; diagnostic=@js($d->diagnostic)">Diagnostic Gör</x-ui.button>
                                    @if($d->user_id)
                                        <a href="{{ route('admin.updates.index', ['scope_key' => 'user', 'user_id' => $d->user_id]) }}"><x-ui.button variant="secondary" type="button">Force Update</x-ui.button></a>
                                    @else
                                        <x-ui.button variant="secondary" type="button" disabled>Force Update</x-ui.button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <x-slot name="footer">{{ $devices->links() }}</x-slot>
                </x-ui.table>
            @endif
        </x-ui.card>

        <x-ui.drawer>
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-lg font-semibold">Device Diagnostic</h3>
                <x-ui.button variant="secondary" type="button" @click="drawer=false">Kapat</x-ui.button>
            </div>
            <template x-if="diagnostic">
                <div class="space-y-4">
                    <x-ui.card title="Device metadata">
                        <x-ui.code-block id="diag-metadata" x-text="JSON.stringify(diagnostic.metadata, null, 2)"></x-ui.code-block>
                    </x-ui.card>

                    <x-ui.card title="Son heartbeat">
                        <template x-if="diagnostic.last_heartbeat">
                            <x-ui.code-block id="diag-heartbeat" x-text="JSON.stringify(diagnostic.last_heartbeat, null, 2)"></x-ui.code-block>
                        </template>
                        <template x-if="!diagnostic.last_heartbeat">
                            <p class="text-sm text-slate-500">Heartbeat kaydı bulunamadı.</p>
                        </template>
                    </x-ui.card>

                    <x-ui.card title="Son 20 log">
                        <template x-if="diagnostic.recent_logs && diagnostic.recent_logs.length">
                            <x-ui.code-block id="diag-logs" x-text="JSON.stringify(diagnostic.recent_logs, null, 2)"></x-ui.code-block>
                        </template>
                        <template x-if="!diagnostic.recent_logs || !diagnostic.recent_logs.length">
                            <p class="text-sm text-slate-500">Log kaydı bulunamadı.</p>
                        </template>
                    </x-ui.card>

                    <x-ui.card title="Compatibility bilgisi">
                        <x-ui.code-block id="diag-compat" x-text="JSON.stringify(diagnostic.compatibility, null, 2)"></x-ui.code-block>
                    </x-ui.card>

                    <x-ui.card title="Cache bilgisi">
                        <x-ui.code-block id="diag-cache" x-text="JSON.stringify(diagnostic.cache, null, 2)"></x-ui.code-block>
                    </x-ui.card>

                    <div class="flex flex-wrap gap-2">
                        <x-ui.copy-button target="#diag-metadata">Metadata Kopyala</x-ui.copy-button>
                        <x-ui.copy-button target="#diag-heartbeat">Heartbeat Kopyala</x-ui.copy-button>
                        <x-ui.copy-button target="#diag-logs">Log Kopyala</x-ui.copy-button>
                        <x-ui.copy-button target="#diag-compat">Compatibility Kopyala</x-ui.copy-button>
                        <x-ui.copy-button target="#diag-cache">Cache Kopyala</x-ui.copy-button>
                    </div>
                </div>
            </template>
        </x-ui.drawer>
    </x-ui.app-shell>
</x-app-layout>


