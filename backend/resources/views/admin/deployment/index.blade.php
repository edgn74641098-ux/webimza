<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Add-in Dagitimi" subtitle="Manifest uretin, indirin ve Microsoft 365 dagitimini yonetin." />
    </x-slot>

    <x-ui.app-shell>
        @if(session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if(session('error'))
            <x-ui.alert type="danger">{{ session('error') }}</x-ui.alert>
        @endif

        <x-ui.card title="1. Mevcut Add-in Config">
            <form method="POST" action="{{ route('admin.deployment.update') }}" class="grid grid-cols-1 gap-3 md:grid-cols-2">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Add-in adi <span class="text-rose-600">*</span></label>
                    <x-ui.input name="display_name" :value="old('display_name', $config->display_name)" required />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Provider <span class="text-rose-600">*</span></label>
                    <x-ui.input name="provider_name" :value="old('provider_name', $config->provider_name)" required />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Manifest ID</label>
                    <x-ui.input name="manifest_id" :value="old('manifest_id', $config->manifest_id)" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Version <span class="text-rose-600">*</span></label>
                    <x-ui.input name="manifest_version" :value="old('manifest_version', $config->manifest_version)" required />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">API URL <span class="text-rose-600">*</span></label>
                    <x-ui.input name="api_base_url" :value="old('api_base_url', $config->api_base_url)" required />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Taskpane URL <span class="text-rose-600">*</span></label>
                    <x-ui.input name="taskpane_url" :value="old('taskpane_url', $config->taskpane_url)" required />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Autorun URL <span class="text-rose-600">*</span></label>
                    <x-ui.input name="autorun_url" :value="old('autorun_url', $config->autorun_url)" required />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Support URL</label>
                    <x-ui.input name="support_url" :value="old('support_url', $config->support_url)" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Icon URL</label>
                    <x-ui.input name="icon_url" :value="old('icon_url', $config->icon_url)" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Highres Icon URL</label>
                    <x-ui.input name="highres_icon_url" :value="old('highres_icon_url', $config->highres_icon_url)" />
                </div>
                <div class="md:col-span-2">
                    <p class="mb-2 text-xs text-slate-500"><span class="text-rose-600">*</span> zorunlu alanlar</p>
                    <x-ui.button type="submit">Config Kaydet</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <x-ui.card title="2. Validation Checklist">
                <div class="space-y-2">
                    @foreach($validationChecklist as $item)
                        <div class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <span>{{ $item['label'] }}</span>
                                <x-ui.badge :status="$item['ok'] ? 'active' : 'inactive'">{{ $item['ok'] ? 'OK' : 'Hata' }}</x-ui.badge>
                            </div>
                            <div class="mt-1 text-xs text-slate-500">{{ $item['detail'] }}</div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            <x-ui.card title="3. Build Actions">
                <div class="space-y-3">
                    <div class="rounded-lg border border-slate-200 p-3">
                        <p class="mb-2 text-sm font-medium text-slate-900">Kisisel test manifesti</p>
                        <p class="mb-3 text-xs text-slate-500">Outlook custom add-in import icindir. Manual imza ekleme, yenileme ve debug ekranini icerir; otomatik compose eventi icermez.</p>
                        <form method="POST" action="{{ route('admin.deployment.build') }}">@csrf<input type="hidden" name="build_type" value="manual_basic"><x-ui.button type="submit" class="w-full">Kisisel Outlook Test Manifesti olustur</x-ui.button></form>
                    </div>
                    <div class="rounded-lg border border-blue-200 bg-blue-50 p-3">
                        <p class="mb-2 text-sm font-medium text-blue-950">M365 otomatik manifest</p>
                        <p class="mb-3 text-xs text-blue-800">Yeni mail acilinca otomatik imza ekleme icin LaunchEvent icerir. Microsoft 365 Admin Center uzerinden dagitim icin kullanin.</p>
                        <form method="POST" action="{{ route('admin.deployment.build') }}">@csrf<input type="hidden" name="build_type" value="automatic_event"><x-ui.button type="submit" class="w-full" variant="secondary">M365 Admin Otomatik Manifest olustur</x-ui.button></form>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <x-ui.card title="4. Download">
                @if($latestBuild)
                    <div class="space-y-2">
                        <a href="{{ route('admin.deployment.manifest', $latestBuild) }}"><x-ui.button type="button" class="w-full" variant="secondary">Manifest indir</x-ui.button></a>
                        <a href="{{ route('admin.deployment.package', $latestBuild) }}"><x-ui.button type="button" class="w-full" variant="secondary">Paket indir</x-ui.button></a>
                    </div>
                @else
                    <x-ui.empty-state title="Indirilebilir build yok" description="Once build olusturun, sonra manifest ve paket indirebilirsiniz." />
                @endif
            </x-ui.card>

            <x-ui.card title="Build Gecmisi">
                @if($builds->isEmpty())
                    <x-ui.empty-state title="Build gecmisi bos" description="Ilk build sonrasi kayitlar burada listelenir." />
                @else
                    <x-ui.table>
                        <x-slot name="head"><tr><th>ID</th><th>Tip</th><th>Versiyon</th><th>Durum</th><th>Tarih</th></tr></x-slot>
                        @foreach($builds as $build)
                            <tr>
                                <td>#{{ $build->id }}</td>
                                <td>{{ $build->build_type === 'automatic_event' ? 'M365 otomatik' : 'Kisisel test' }}</td>
                                <td>{{ $build->version ?? '-' }}</td>
                                <td><x-ui.badge status="default">{{ $build->status ?? '-' }}</x-ui.badge></td>
                                <td>{{ optional($build->created_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </x-ui.table>
                @endif
            </x-ui.card>
        </div>

        <x-ui.card title="5. Microsoft 365 Dagitim Adimlari">
            <ol class="list-decimal space-y-1 pl-5 text-sm text-slate-700">
                <li>Microsoft 365 Admin Center ac.</li>
                <li>Settings bolumune gir.</li>
                <li>Integrated Apps sec.</li>
                <li>Upload custom app sec.</li>
                <li>Manifest dosyasini yukle.</li>
                <li>Test grubuna ata.</li>
                <li>Deploy et.</li>
            </ol>
        </x-ui.card>

        <x-ui.card title="API Test URLleri">
            <div class="space-y-2 text-sm text-slate-700">
                <div class="rounded-lg border border-slate-200 px-3 py-2">
                    <div class="font-medium">Saglik kontrolu (GET)</div>
                    <div class="mt-1 font-mono text-xs text-slate-600">{{ rtrim((string) ($config->api_base_url ?? ''), '/') }}/addin/ping</div>
                </div>
                <div class="rounded-lg border border-slate-200 px-3 py-2">
                    <div class="font-medium">Imza kontrolu (POST)</div>
                    <div class="mt-1 font-mono text-xs text-slate-600">{{ rtrim((string) ($config->api_base_url ?? ''), '/') }}/addin/signature/check</div>
                </div>
                <div class="rounded-lg border border-slate-200 px-3 py-2">
                    <div class="font-medium">Manual API Test Sayfasi</div>
                    <div class="mt-1 font-mono text-xs text-slate-600">{{ url('/addin-manual-test.html') }}</div>
                </div>
            </div>
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
