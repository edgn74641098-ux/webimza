@php
    $settingSections = [
        [
            'title' => 'Uygulama',
            'description' => 'Panelin temel kimligi, ortam modu ve zaman dilimi.',
            'fields' => [
                ['name' => 'APP_NAME', 'label' => 'Uygulama adi', 'type' => 'text', 'span' => 'md:col-span-2'],
                ['name' => 'APP_ENV', 'label' => 'Ortam', 'type' => 'select', 'options' => ['production', 'staging', 'local']],
                ['name' => 'APP_DEBUG', 'label' => 'Debug modu', 'type' => 'select', 'options' => ['false', 'true']],
                ['name' => 'APP_URL', 'label' => 'Uygulama URL', 'type' => 'url', 'span' => 'md:col-span-2'],
                ['name' => 'APP_TIMEZONE', 'label' => 'Zaman dilimi', 'type' => 'select', 'options' => ['Europe/Istanbul', 'UTC', 'Europe/London', 'Europe/Berlin', 'America/New_York']],
                ['name' => 'APP_LOCALE', 'label' => 'Dil', 'type' => 'text'],
                ['name' => 'APP_FALLBACK_LOCALE', 'label' => 'Yedek dil', 'type' => 'text'],
            ],
        ],
        [
            'title' => 'Calisma',
            'description' => 'Log, cache, queue ve oturum davranislari.',
            'fields' => [
                ['name' => 'LOG_LEVEL', 'label' => 'Log seviyesi', 'type' => 'select', 'options' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency']],
                ['name' => 'CACHE_STORE', 'label' => 'Cache store', 'type' => 'text'],
                ['name' => 'QUEUE_CONNECTION', 'label' => 'Queue connection', 'type' => 'text'],
                ['name' => 'SESSION_DRIVER', 'label' => 'Session driver', 'type' => 'text'],
                ['name' => 'SESSION_LIFETIME', 'label' => 'Session lifetime (dk)', 'type' => 'number'],
            ],
        ],
        [
            'title' => 'Mail',
            'description' => 'Sistem bildirimleri icin mail cikis ayarlari.',
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

    $statusItems = [
        ['label' => 'Uygulama saati', 'value' => $appNow->format('Y-m-d H:i:s T')],
        ['label' => 'UTC saati', 'value' => $utcNow->format('Y-m-d H:i:s T')],
        ['label' => 'PHP timezone', 'value' => $phpTimezone],
        ['label' => 'Database', 'value' => $databaseConnection.' / '.$databaseDriver],
        ['label' => 'Cache', 'value' => $cacheStore],
        ['label' => 'Queue', 'value' => $queueConnection],
        ['label' => 'Session', 'value' => $sessionDriver],
        ['label' => 'Log channel', 'value' => $logChannel],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Ayarlar" subtitle="Sistem konfigürasyonu, zaman dilimi ve add-in dağıtım sagligi.">
            <x-slot name="actions">
                <a href="{{ route('admin.deployment.index') }}"><x-ui.button>Add-in Dagitimi</x-ui.button></a>
                <a href="{{ route('admin.dashboard') }}"><x-ui.button variant="secondary" type="button">Dashboard</x-ui.button></a>
            </x-slot>
        </x-ui.page-header>
    </x-slot>

    <x-ui.app-shell>
        @if(session('success'))
            <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
        @endif
        @if($errors->any())
            <x-ui.alert type="error">Ayarlar kaydedilemedi. Lutfen isaretli alanlari kontrol edin.</x-ui.alert>
        @endif

        <section class="grid grid-cols-1 gap-3 lg:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Timezone</p>
                <p class="mt-2 text-xl font-semibold text-slate-950">{{ $timezone }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $appNow->format('Y-m-d H:i:s T') }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Ortam</p>
                <div class="mt-2 flex items-center gap-2">
                    <x-ui.badge :status="$environment === 'production' ? 'active' : 'warning'">{{ $environment }}</x-ui.badge>
                    <x-ui.badge :status="$debugEnabled ? 'warning' : 'active'">debug {{ $debugEnabled ? 'on' : 'off' }}</x-ui.badge>
                </div>
                <p class="mt-2 break-all text-xs text-slate-500">{{ $appUrl }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Add-in build</p>
                <p class="mt-2 text-xl font-semibold text-slate-950">{{ $latestBuild?->version ?? '-' }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $latestBuild?->build_type ?? 'build yok' }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Public dosyalar</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <x-ui.badge :status="$manifestLatestExists ? 'active' : 'inactive'">manifest {{ $manifestLatestExists ? 'var' : 'yok' }}</x-ui.badge>
                    <x-ui.badge :status="$addinPublicExists ? 'active' : 'inactive'">addin {{ $addinPublicExists ? 'var' : 'yok' }}</x-ui.badge>
                </div>
                <p class="mt-2 text-xs text-slate-500">Version {{ $addinConfig?->manifest_version ?? '-' }}</p>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
            <x-ui.card>
                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Sistem ayarlari</h2>
                            <p class="mt-1 max-w-2xl text-sm text-slate-500">Bu form secili operasyonel degerleri <span class="font-mono">backend/.env</span> dosyasina yazar. Kayit oncesi otomatik yedek alinir.</p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <a href="{{ route('admin.settings.index') }}"><x-ui.button variant="secondary" type="button">Vazgec</x-ui.button></a>
                            <x-ui.button type="submit">Kaydet</x-ui.button>
                        </div>
                    </div>

                    @foreach($settingSections as $section)
                        <section class="grid grid-cols-1 gap-4 rounded-lg border border-slate-200 p-4 lg:grid-cols-[220px_minmax(0,1fr)]">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-950">{{ $section['title'] }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $section['description'] }}</p>
                            </div>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                @foreach($section['fields'] as $field)
                                    @php
                                        $name = $field['name'];
                                        $type = $field['type'];
                                        $span = $field['span'] ?? '';
                                        $value = old($name, $envSettings[$name] ?? '');
                                    @endphp
                                    <div class="{{ $span }}">
                                        <label class="mb-1 flex items-center justify-between gap-2 text-sm font-medium text-slate-700" for="{{ $name }}">
                                            <span>{{ $field['label'] }}</span>
                                            <span class="font-mono text-[10px] uppercase text-slate-400">{{ $name }}</span>
                                        </label>
                                        @if($type === 'select')
                                            <x-ui.select id="{{ $name }}" name="{{ $name }}" required>
                                                @foreach($field['options'] as $option)
                                                    <option value="{{ $option }}" @selected((string) $value === (string) $option)>{{ $option }}</option>
                                                @endforeach
                                            </x-ui.select>
                                        @else
                                            <x-ui.input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" :value="$value" @if(!str_starts_with($name, 'MAIL_')) required @endif />
                                        @endif
                                        @error($name)
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach

                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        APP_KEY, database sifreleri ve servis secret degerleri bu ekrandan degistirilmez. Bu alanlar sadece sunucu erisimi olan yoneticiler tarafindan guncellenmelidir.
                    </div>

                    <div class="sticky bottom-3 z-10 flex flex-col gap-3 rounded-lg border border-slate-200 bg-white/95 p-3 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs text-slate-500">Kaydetme islemi .env yedegi olusturur ve Laravel config/cache temizler.</p>
                        <x-ui.button type="submit">Ayarlari Kaydet</x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            <aside class="space-y-4">
                <x-ui.card title="Canli Durum" subtitle="Config degerlerinin su anki okumasi.">
                    <div class="space-y-2 text-sm">
                        @foreach($statusItems as $item)
                            <div class="rounded-lg border border-slate-200 px-3 py-2">
                                <div class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $item['label'] }}</div>
                                <div class="mt-1 break-words font-semibold text-slate-900">{{ $item['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>

                <x-ui.card title="Add-in Konumu" subtitle="Outlook eklentisinin yayinlanan endpointleri.">
                    <div class="space-y-2 text-sm">
                        <div class="rounded-lg border border-slate-200 px-3 py-2">
                            <div class="text-slate-500">API URL</div>
                            <strong class="block break-all">{{ $addinConfig?->api_base_url ?? '-' }}</strong>
                        </div>
                        <div class="rounded-lg border border-slate-200 px-3 py-2">
                            <div class="text-slate-500">Taskpane URL</div>
                            <strong class="block break-all">{{ $addinConfig?->taskpane_url ?? '-' }}</strong>
                        </div>
                        <div class="rounded-lg border border-slate-200 px-3 py-2">
                            <div class="text-slate-500">Son build durumu</div>
                            <strong class="block break-all">{{ $latestBuild?->status ?? '-' }}</strong>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card title="Kisa Yollar">
                    <div class="grid grid-cols-1 gap-2">
                        <a href="{{ route('admin.deployment.index') }}"><x-ui.button class="w-full">Add-in Dagitimi</x-ui.button></a>
                        <a href="{{ route('admin.templates.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Sablonlar</x-ui.button></a>
                        <a href="{{ route('admin.assignments.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Atamalar</x-ui.button></a>
                        <a href="{{ route('admin.logs.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Loglar</x-ui.button></a>
                    </div>
                </x-ui.card>

                <x-ui.card title="Bakim">
                    <x-ui.code-block>cd /var/www/webimza/backend
sudo -u www-data php artisan optimize:clear
npm run build</x-ui.code-block>
                </x-ui.card>
            </aside>
        </div>
    </x-ui.app-shell>
</x-app-layout>
