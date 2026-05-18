@php
    $sections = [
        'general' => [
            'label' => 'Genel',
            'title' => 'Uygulama ayarlari',
            'description' => 'Panel kimligi, calisma ortami, URL ve zaman dilimi.',
            'fields' => [
                ['name' => 'APP_NAME', 'label' => 'Uygulama adi', 'type' => 'text', 'span' => 'lg:col-span-2'],
                ['name' => 'APP_URL', 'label' => 'Uygulama URL', 'type' => 'url', 'span' => 'lg:col-span-2'],
                ['name' => 'APP_ENV', 'label' => 'Ortam', 'type' => 'select', 'options' => ['production', 'staging', 'local']],
                ['name' => 'APP_DEBUG', 'label' => 'Debug', 'type' => 'select', 'options' => ['false', 'true']],
                ['name' => 'APP_TIMEZONE', 'label' => 'Zaman dilimi', 'type' => 'select', 'options' => ['Europe/Istanbul', 'UTC', 'Europe/London', 'Europe/Berlin', 'America/New_York']],
                ['name' => 'APP_LOCALE', 'label' => 'Dil', 'type' => 'text'],
                ['name' => 'APP_FALLBACK_LOCALE', 'label' => 'Yedek dil', 'type' => 'text'],
            ],
        ],
        'runtime' => [
            'label' => 'Calisma',
            'title' => 'Runtime ayarlari',
            'description' => 'Log, cache, kuyruk ve oturum davranislari.',
            'fields' => [
                ['name' => 'LOG_LEVEL', 'label' => 'Log seviyesi', 'type' => 'select', 'options' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency']],
                ['name' => 'CACHE_STORE', 'label' => 'Cache store', 'type' => 'text'],
                ['name' => 'QUEUE_CONNECTION', 'label' => 'Queue connection', 'type' => 'text'],
                ['name' => 'SESSION_DRIVER', 'label' => 'Session driver', 'type' => 'text'],
                ['name' => 'SESSION_LIFETIME', 'label' => 'Session lifetime', 'type' => 'number'],
            ],
        ],
        'microsoft365' => [
            'label' => 'Microsoft 365',
            'title' => 'Entra / Graph senkronizasyonu',
            'description' => 'Modern auth ile kullanici, departman, grup ve grup uyeliklerini otomatik ice aktarir.',
            'fields' => [
                ['name' => 'ENTRA_TENANT_ID', 'label' => 'Tenant ID veya domain', 'type' => 'text', 'span' => 'lg:col-span-2', 'required' => false],
                ['name' => 'ENTRA_CLIENT_ID', 'label' => 'Client ID', 'type' => 'text', 'span' => 'lg:col-span-2', 'required' => false],
                ['name' => 'ENTRA_GROUP_PREFIX', 'label' => 'Grup prefix filtresi', 'type' => 'text', 'required' => false, 'hint' => 'Bos kalirsa tum gruplar alinir.'],
            ],
        ],
        'mail' => [
            'label' => 'Mail',
            'title' => 'Mail cikisi',
            'description' => 'Bildirim ve sistem mail ayarlari.',
            'fields' => [
                ['name' => 'MAIL_MAILER', 'label' => 'Mailer', 'type' => 'text'],
                ['name' => 'MAIL_HOST', 'label' => 'Host', 'type' => 'text'],
                ['name' => 'MAIL_PORT', 'label' => 'Port', 'type' => 'number'],
                ['name' => 'MAIL_USERNAME', 'label' => 'Kullanici adi', 'type' => 'text'],
                ['name' => 'MAIL_FROM_ADDRESS', 'label' => 'Gonderen e-posta', 'type' => 'email'],
                ['name' => 'MAIL_FROM_NAME', 'label' => 'Gonderen adi', 'type' => 'text'],
            ],
        ],
    ];

    $health = [
        ['label' => 'Uygulama saati', 'value' => $appNow->format('Y-m-d H:i:s T')],
        ['label' => 'UTC', 'value' => $utcNow->format('Y-m-d H:i:s T')],
        ['label' => 'PHP timezone', 'value' => $phpTimezone],
        ['label' => 'Database', 'value' => $databaseConnection.' / '.$databaseDriver],
        ['label' => 'Cache', 'value' => $cacheStore],
        ['label' => 'Queue', 'value' => $queueConnection],
        ['label' => 'Session', 'value' => $sessionDriver],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Ayarlar" subtitle="Sistem konfigürasyonu ve add-in yayin durumu.">
            <x-slot name="actions">
                <a href="{{ route('admin.deployment.index') }}"><x-ui.button>Add-in Dagitimi</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <x-ui.app-shell>
        @if(session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error">Ayarlar kaydedilemedi. Lutfen alanlari kontrol edin.</x-ui.alert>
        @endif
        @error('entra_sync')
            <x-ui.alert type="error">{{ $message }}</x-ui.alert>
        @enderror
        @error('microsoft365')
            <x-ui.alert type="error">{{ $message }}</x-ui.alert>
        @enderror

        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="grid grid-cols-1 divide-y divide-slate-200 lg:grid-cols-[240px_minmax(0,1fr)_320px] lg:divide-x lg:divide-y-0">
                <aside class="bg-slate-50/70 p-4">
                    <div class="mb-5">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kontrol Merkezi</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-950">Sistem Ayarlari</h2>
                    </div>

                    <nav class="space-y-1">
                        @foreach($sections as $id => $section)
                            <a href="#{{ $id }}" class="flex items-center justify-between rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-white hover:text-blue-900">
                                <span>{{ $section['label'] }}</span>
                                <span class="text-xs text-slate-400">{{ count($section['fields']) }}</span>
                            </a>
                        @endforeach
                    </nav>

                    <div class="mt-6 rounded-md border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900">
                        APP_KEY, database sifreleri, kullanici sifreleri ve Microsoft secret degerleri bu ekrandan kaydedilmez.
                    </div>
                </aside>

                <main class="min-w-0">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-slate-950">Duzenlenebilir .env ayarlari</p>
                                <p class="mt-1 text-sm text-slate-500">Kayit oncesi otomatik yedek alinir, ardindan config/cache temizlenir.</p>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.settings.index') }}"><x-ui.button variant="secondary" type="button">Vazgec</x-ui.button></a>
                                <x-ui.button type="submit">Kaydet</x-ui.button>
                            </div>
                        </div>

                        <div class="divide-y divide-slate-200">
                            @foreach($sections as $id => $section)
                                <section id="{{ $id }}" class="scroll-mt-24 px-5 py-6">
                                    <div class="mb-4 max-w-2xl">
                                        <h3 class="text-base font-semibold text-slate-950">{{ $section['title'] }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $section['description'] }}</p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                                        @foreach($section['fields'] as $field)
                                            @php
                                                $name = $field['name'];
                                                $value = old($name, $envSettings[$name] ?? '');
                                                $type = $field['type'];
                                                $span = $field['span'] ?? '';
                                                $required = $field['required'] ?? (! str_starts_with($name, 'MAIL_') && ! str_starts_with($name, 'ENTRA_'));
                                            @endphp
                                            <div class="{{ $span }}">
                                                <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $field['label'] }}</label>
                                                @if($type === 'select')
                                                    <x-ui.select id="{{ $name }}" name="{{ $name }}" :required="$required">
                                                        @foreach($field['options'] as $option)
                                                            <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ $option }}</option>
                                                        @endforeach
                                                    </x-ui.select>
                                                @else
                                                    <x-ui.input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" :value="$value" :required="$required" autocomplete="off" />
                                                @endif
                                                @if(! empty($field['hint']))
                                                    <p class="mt-1 text-xs text-slate-500">{{ $field['hint'] }}</p>
                                                @endif
                                                <div class="mt-1 font-mono text-[11px] text-slate-400">{{ $name }}</div>
                                                @error($name)
                                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>

                        <div class="sticky bottom-0 flex flex-col gap-3 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-slate-500">Degisiklikler <span class="font-mono">backend/.env</span> dosyasina yazilir.</p>
                            <x-ui.button type="submit">Ayarlari Kaydet</x-ui.button>
                        </div>
                    </form>
                </main>

                <aside class="bg-slate-50/60 p-4">
                    <div class="space-y-4">
                        <section>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Ozet</p>
                            <div class="grid grid-cols-2 gap-2">
                                <div class="rounded-md border border-slate-200 bg-white p-3">
                                    <div class="text-xs text-slate-500">Timezone</div>
                                    <div class="mt-1 break-words text-sm font-semibold text-slate-950">{{ $timezone }}</div>
                                </div>
                                <div class="rounded-md border border-slate-200 bg-white p-3">
                                    <div class="text-xs text-slate-500">Ortam</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-950">{{ $environment }}</div>
                                </div>
                                <div class="rounded-md border border-slate-200 bg-white p-3">
                                    <div class="text-xs text-slate-500">Debug</div>
                                    <x-ui.badge :status="$debugEnabled ? 'warning' : 'active'">{{ $debugEnabled ? 'acik' : 'kapali' }}</x-ui.badge>
                                </div>
                                <div class="rounded-md border border-slate-200 bg-white p-3">
                                    <div class="text-xs text-slate-500">Build</div>
                                    <div class="mt-1 text-sm font-semibold text-slate-950">{{ $latestBuild?->version ?? '-' }}</div>
                                </div>
                            </div>
                        </section>

                        <section>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Sistem</p>
                            <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
                                @foreach($health as $item)
                                    <div class="border-b border-slate-100 px-3 py-2 last:border-b-0">
                                        <div class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ $item['label'] }}</div>
                                        <div class="mt-0.5 break-words text-sm text-slate-800">{{ $item['value'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Add-in</p>
                            <div class="space-y-2">
                                <div class="rounded-md border border-slate-200 bg-white p-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm text-slate-600">manifest-latest.xml</span>
                                        <x-ui.badge :status="$manifestLatestExists ? 'active' : 'inactive'">{{ $manifestLatestExists ? 'var' : 'yok' }}</x-ui.badge>
                                    </div>
                                </div>
                                <div class="rounded-md border border-slate-200 bg-white p-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm text-slate-600">public/addin</span>
                                        <x-ui.badge :status="$addinPublicExists ? 'active' : 'inactive'">{{ $addinPublicExists ? 'var' : 'yok' }}</x-ui.badge>
                                    </div>
                                </div>
                                <div class="rounded-md border border-slate-200 bg-white p-3">
                                    <div class="text-xs text-slate-500">API URL</div>
                                    <div class="mt-1 break-all text-sm font-medium text-slate-900">{{ $addinConfig?->api_base_url ?? '-' }}</div>
                                </div>
                            </div>
                        </section>

                        <section>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Microsoft 365 Sync</p>
                            <div class="space-y-2 rounded-md border border-slate-200 bg-white p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm text-slate-600">Modern auth oturumu</span>
                                    <x-ui.badge :status="$graphSessionConnected ? 'active' : 'inactive'">{{ $graphSessionConnected ? 'bagli' : 'kapali' }}</x-ui.badge>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-sm text-slate-600">Kimlik bilgileri</span>
                                    <x-ui.badge :status="$graphSyncConfigured ? 'active' : 'warning'">{{ $graphSyncConfigured ? 'hazir' : 'eksik' }}</x-ui.badge>
                                </div>
                                @if($graphSyncConfigured)
                                    <a href="{{ route('admin.microsoft365.connect') }}" class="ui-btn-primary mt-2 w-full justify-center" target="_blank" rel="noopener">
                                        Microsoft ile Baglan
                                    </a>
                                @else
                                    <div class="mt-2 rounded-md border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-900">
                                        Baglanmak icin once Tenant ID ve Client ID alanlarini doldurup ayarlari kaydedin.
                                    </div>
                                @endif
                                @if($graphSessionConnected)
                                    <a href="{{ route('admin.microsoft365.directory') }}" class="ui-btn-secondary mt-2 w-full justify-center">
                                        Kullanicilari ve Gruplari Sec
                                    </a>
                                @endif
                                <div class="rounded-md bg-slate-50 p-2 text-[11px] leading-5 text-slate-500">
                                    Baglanti URL: <a class="break-all font-mono text-blue-800 underline" href="{{ route('admin.microsoft365.connect') }}" target="_blank" rel="noopener">{{ route('admin.microsoft365.connect') }}</a><br>
                                    Redirect URI: <span class="break-all font-mono text-slate-700">{{ route('admin.microsoft365.callback') }}</span>
                                </div>
                                <p class="text-xs leading-5 text-slate-500">Kimlik bilgisi kaydedilmez; Microsoft modern auth sonrasi gecici oturumla listeleme yapilir.</p>
                            </div>
                        </section>

                        <section>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Kisa yollar</p>
                            <div class="grid grid-cols-1 gap-2">
                                <a href="{{ route('admin.deployment.index') }}"><x-ui.button class="w-full">Add-in Dagitimi</x-ui.button></a>
                                <a href="{{ route('admin.templates.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Sablonlar</x-ui.button></a>
                                <a href="{{ route('admin.logs.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Loglar</x-ui.button></a>
                            </div>
                        </section>
                    </div>
                </aside>
            </div>
        </div>
    </x-ui.app-shell>
</x-app-layout>
