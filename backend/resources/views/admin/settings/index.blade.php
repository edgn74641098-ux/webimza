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
