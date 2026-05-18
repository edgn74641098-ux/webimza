<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Ayarlar" subtitle="Sistem saati, uygulama konfigürasyonu ve add-in dağıtım durumunu kontrol edin.">
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
            <x-ui.alert type="error">Ayarlar kaydedilemedi. Lutfen form alanlarini kontrol edin.</x-ui.alert>
        @endif

        <x-ui.card title="Duzenlenebilir Sistem Ayarlari" subtitle="Bu alanlar backend/.env dosyasina yazilir ve config cache otomatik temizlenir.">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-900">Uygulama</h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">APP_NAME</label>
                            <x-ui.input name="APP_NAME" :value="old('APP_NAME', $envSettings['APP_NAME'])" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">APP_ENV</label>
                            <x-ui.select name="APP_ENV" required>
                                @foreach(['production', 'staging', 'local'] as $option)
                                    <option value="{{ $option }}" @selected(old('APP_ENV', $envSettings['APP_ENV']) === $option)>{{ $option }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">APP_DEBUG</label>
                            <x-ui.select name="APP_DEBUG" required>
                                <option value="false" @selected(old('APP_DEBUG', $envSettings['APP_DEBUG']) === 'false')>false</option>
                                <option value="true" @selected(old('APP_DEBUG', $envSettings['APP_DEBUG']) === 'true')>true</option>
                            </x-ui.select>
                        </div>
                        <div class="xl:col-span-2">
                            <label class="mb-1 block text-sm font-medium text-slate-700">APP_URL</label>
                            <x-ui.input name="APP_URL" type="url" :value="old('APP_URL', $envSettings['APP_URL'])" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">APP_TIMEZONE</label>
                            <x-ui.select name="APP_TIMEZONE" required>
                                @foreach(['Europe/Istanbul', 'UTC', 'Europe/London', 'Europe/Berlin', 'America/New_York'] as $option)
                                    <option value="{{ $option }}" @selected(old('APP_TIMEZONE', $envSettings['APP_TIMEZONE']) === $option)>{{ $option }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">APP_LOCALE</label>
                            <x-ui.input name="APP_LOCALE" :value="old('APP_LOCALE', $envSettings['APP_LOCALE'])" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">APP_FALLBACK_LOCALE</label>
                            <x-ui.input name="APP_FALLBACK_LOCALE" :value="old('APP_FALLBACK_LOCALE', $envSettings['APP_FALLBACK_LOCALE'])" required />
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-900">Calisma Ayarlari</h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">LOG_LEVEL</label>
                            <x-ui.select name="LOG_LEVEL" required>
                                @foreach(['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'] as $option)
                                    <option value="{{ $option }}" @selected(old('LOG_LEVEL', $envSettings['LOG_LEVEL']) === $option)>{{ $option }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">CACHE_STORE</label>
                            <x-ui.input name="CACHE_STORE" :value="old('CACHE_STORE', $envSettings['CACHE_STORE'])" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">QUEUE_CONNECTION</label>
                            <x-ui.input name="QUEUE_CONNECTION" :value="old('QUEUE_CONNECTION', $envSettings['QUEUE_CONNECTION'])" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">SESSION_DRIVER</label>
                            <x-ui.input name="SESSION_DRIVER" :value="old('SESSION_DRIVER', $envSettings['SESSION_DRIVER'])" required />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">SESSION_LIFETIME</label>
                            <x-ui.input name="SESSION_LIFETIME" type="number" min="1" :value="old('SESSION_LIFETIME', $envSettings['SESSION_LIFETIME'])" required />
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-900">Mail Ayarlari</h3>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">MAIL_MAILER</label>
                            <x-ui.input name="MAIL_MAILER" :value="old('MAIL_MAILER', $envSettings['MAIL_MAILER'])" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">MAIL_HOST</label>
                            <x-ui.input name="MAIL_HOST" :value="old('MAIL_HOST', $envSettings['MAIL_HOST'])" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">MAIL_PORT</label>
                            <x-ui.input name="MAIL_PORT" type="number" min="1" max="65535" :value="old('MAIL_PORT', $envSettings['MAIL_PORT'])" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">MAIL_USERNAME</label>
                            <x-ui.input name="MAIL_USERNAME" :value="old('MAIL_USERNAME', $envSettings['MAIL_USERNAME'])" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">MAIL_FROM_ADDRESS</label>
                            <x-ui.input name="MAIL_FROM_ADDRESS" type="email" :value="old('MAIL_FROM_ADDRESS', $envSettings['MAIL_FROM_ADDRESS'])" />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">MAIL_FROM_NAME</label>
                            <x-ui.input name="MAIL_FROM_NAME" :value="old('MAIL_FROM_NAME', $envSettings['MAIL_FROM_NAME'])" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-slate-500">DB sifreleri ve APP_KEY gibi hassas anahtarlar guvenlik icin bu formdan duzenlenmez.</p>
                    <x-ui.button type="submit">Ayarlari Kaydet</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <x-ui.card title="Saat ve Zaman Dilimi" subtitle="Paneldeki tarih/saat gosterimleri bu ayara gore hesaplanir.">
                <div class="space-y-3 text-sm">
                    <div class="rounded-lg border border-slate-200 px-3 py-2">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Uygulama timezone</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $timezone }}</div>
                    </div>
                    <div class="rounded-lg border border-slate-200 px-3 py-2">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">Uygulama saati</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $appNow->format('Y-m-d H:i:s T') }}</div>
                    </div>
                    <div class="rounded-lg border border-slate-200 px-3 py-2">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">UTC saati</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $utcNow->format('Y-m-d H:i:s T') }}</div>
                    </div>
                    <div class="rounded-lg border border-slate-200 px-3 py-2">
                        <div class="text-xs font-medium uppercase tracking-wide text-slate-500">PHP timezone</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $phpTimezone }}</div>
                    </div>
                </div>
                <x-ui.alert type="info" class="mt-4">
                    Sunucuda kalici ayar icin <strong>backend/.env</strong> icinde <strong>APP_TIMEZONE=Europe/Istanbul</strong> kullanin ve config cache temizleyin.
                </x-ui.alert>
            </x-ui.card>

            <x-ui.card title="Uygulama Ayarlari" subtitle="Canli ortamda kritik Laravel ayarlari.">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">APP_ENV</span><strong>{{ $environment }}</strong></div>
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">APP_DEBUG</span><strong>{{ $debugEnabled ? 'true' : 'false' }}</strong></div>
                    <div class="rounded-lg border border-slate-200 px-3 py-2">
                        <div class="text-slate-500">APP_URL</div>
                        <strong class="block break-all">{{ $appUrl }}</strong>
                    </div>
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">Database</span><strong>{{ $databaseConnection }} / {{ $databaseDriver }}</strong></div>
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">Cache</span><strong>{{ $cacheStore }}</strong></div>
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">Queue</span><strong>{{ $queueConnection }}</strong></div>
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">Session</span><strong>{{ $sessionDriver }}</strong></div>
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">Log channel</span><strong>{{ $logChannel }}</strong></div>
                </div>
            </x-ui.card>

            <x-ui.card title="Add-in Durumu" subtitle="Manifest ve public add-in dosyalari.">
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">Manifest version</span><strong>{{ $addinConfig?->manifest_version ?? '-' }}</strong></div>
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">Son build</span><strong>{{ $latestBuild?->version ?? '-' }}</strong></div>
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">Build tipi</span><strong>{{ $latestBuild?->build_type ?? '-' }}</strong></div>
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">Build durumu</span><strong>{{ $latestBuild?->status ?? '-' }}</strong></div>
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">manifest-latest.xml</span><x-ui.badge :status="$manifestLatestExists ? 'active' : 'inactive'">{{ $manifestLatestExists ? 'Var' : 'Yok' }}</x-ui.badge></div>
                    <div class="flex justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"><span class="text-slate-500">public/addin</span><x-ui.badge :status="$addinPublicExists ? 'active' : 'inactive'">{{ $addinPublicExists ? 'Var' : 'Yok' }}</x-ui.badge></div>
                    <div class="rounded-lg border border-slate-200 px-3 py-2">
                        <div class="text-slate-500">API URL</div>
                        <strong class="block break-all">{{ $addinConfig?->api_base_url ?? '-' }}</strong>
                    </div>
                    <div class="rounded-lg border border-slate-200 px-3 py-2">
                        <div class="text-slate-500">Taskpane URL</div>
                        <strong class="block break-all">{{ $addinConfig?->taskpane_url ?? '-' }}</strong>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <x-ui.card title="Operasyon Kisa Yollari" subtitle="Sik kullanilan yonetim ekranlarina hizli gecis yapin.">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-3">
                <a href="{{ route('admin.deployment.index') }}"><x-ui.button class="w-full">Add-in Dagitimi</x-ui.button></a>
                <a href="{{ route('admin.templates.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Sablonlar</x-ui.button></a>
                <a href="{{ route('admin.assignments.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Atamalar</x-ui.button></a>
                <a href="{{ route('admin.groups.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Gruplar</x-ui.button></a>
                <a href="{{ route('admin.updates.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Guncelleme Yonetimi</x-ui.button></a>
                <a href="{{ route('admin.logs.index') }}"><x-ui.button class="w-full" variant="secondary" type="button">Loglar</x-ui.button></a>
            </div>
        </x-ui.card>

        <x-ui.card title="Bakim Komutlari" subtitle="Deploy sonrasi dogrulama ve cache temizligi icin.">
            <x-ui.code-block>cd /var/www/webimza/backend
sudo -u www-data php artisan optimize:clear
npm run build</x-ui.code-block>
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
