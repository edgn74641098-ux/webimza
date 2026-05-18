<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddinBuild;
use App\Models\AddinConfig;
use App\Services\MicrosoftGraph\GraphDirectorySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class SettingsController extends Controller
{
    public function index()
    {
        $timezone = config('app.timezone');
        $latestBuild = AddinBuild::query()->latest()->first();
        $addinConfig = AddinConfig::query()->first();

        return view('admin.settings.index', [
            'timezone' => $timezone,
            'appNow' => now(),
            'utcNow' => Carbon::now('UTC'),
            'phpTimezone' => date_default_timezone_get(),
            'environment' => config('app.env'),
            'debugEnabled' => (bool) config('app.debug'),
            'appUrl' => config('app.url'),
            'databaseConnection' => config('database.default'),
            'databaseDriver' => DB::connection()->getDriverName(),
            'cacheStore' => config('cache.default'),
            'queueConnection' => config('queue.default'),
            'sessionDriver' => config('session.driver'),
            'logChannel' => config('logging.default'),
            'addinConfig' => $addinConfig,
            'latestBuild' => $latestBuild,
            'manifestLatestPath' => public_path('manifest-latest.xml'),
            'manifestLatestExists' => file_exists(public_path('manifest-latest.xml')),
            'addinPublicPath' => public_path('addin'),
            'addinPublicExists' => is_dir(public_path('addin')),
            'envSettings' => $this->readEditableEnvValues(),
            'graphSyncEnabled' => (bool) config('services.microsoft_graph.sync_enabled'),
            'graphSyncConfigured' => filled(config('services.microsoft_graph.tenant_id'))
                && filled(config('services.microsoft_graph.client_id'))
                && filled(config('services.microsoft_graph.client_secret')),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'APP_NAME' => ['required', 'string', 'max:120'],
            'APP_ENV' => ['required', 'in:local,staging,production'],
            'APP_DEBUG' => ['required', 'in:true,false'],
            'APP_URL' => ['required', 'url', 'max:255'],
            'APP_TIMEZONE' => ['required', 'timezone'],
            'APP_LOCALE' => ['required', 'string', 'max:12'],
            'APP_FALLBACK_LOCALE' => ['required', 'string', 'max:12'],
            'LOG_LEVEL' => ['required', 'in:debug,info,notice,warning,error,critical,alert,emergency'],
            'CACHE_STORE' => ['required', 'string', 'max:60'],
            'QUEUE_CONNECTION' => ['required', 'string', 'max:60'],
            'SESSION_DRIVER' => ['required', 'string', 'max:60'],
            'SESSION_LIFETIME' => ['required', 'integer', 'min:1', 'max:43200'],
            'MAIL_MAILER' => ['nullable', 'string', 'max:60'],
            'MAIL_HOST' => ['nullable', 'string', 'max:255'],
            'MAIL_PORT' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'MAIL_USERNAME' => ['nullable', 'string', 'max:255'],
            'MAIL_FROM_ADDRESS' => ['nullable', 'email', 'max:255'],
            'MAIL_FROM_NAME' => ['nullable', 'string', 'max:255'],
            'ENTRA_SYNC_ENABLED' => ['required', 'in:true,false'],
            'ENTRA_TENANT_ID' => ['nullable', 'string', 'max:255'],
            'ENTRA_CLIENT_ID' => ['nullable', 'string', 'max:255'],
            'ENTRA_CLIENT_SECRET' => ['nullable', 'string', 'max:2048'],
            'ENTRA_SYNC_GROUPS' => ['required', 'in:true,false'],
            'ENTRA_GROUP_PREFIX' => ['nullable', 'string', 'max:120'],
        ]);

        if (blank($data['ENTRA_CLIENT_SECRET'] ?? null)) {
            unset($data['ENTRA_CLIENT_SECRET']);
        }

        $this->writeEnvValues($data);
        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Ayarlar kaydedildi. Yeni konfigürasyon aktif edildi.');
    }

    public function syncEntraDirectory(GraphDirectorySyncService $syncService)
    {
        try {
            $summary = $syncService->sync();
        } catch (Throwable $exception) {
            return redirect()
                ->route('admin.settings.index')
                ->withErrors(['entra_sync' => $exception->getMessage()]);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', sprintf(
                'Microsoft 365 senkronizasyonu tamamlandi. Kullanici: %d yeni / %d guncel / %d atlandi, departman: %d, grup: %d yeni / %d guncel, uyelik: %d.',
                $summary['users_created'],
                $summary['users_updated'],
                $summary['users_skipped'],
                $summary['departments_created'],
                $summary['groups_created'],
                $summary['groups_updated'],
                $summary['memberships_synced'],
            ));
    }

    private function readEditableEnvValues(): array
    {
        $values = $this->parseEnvFile();

        return [
            'APP_NAME' => $values['APP_NAME'] ?? config('app.name', 'Laravel'),
            'APP_ENV' => $values['APP_ENV'] ?? config('app.env', 'production'),
            'APP_DEBUG' => $values['APP_DEBUG'] ?? (config('app.debug') ? 'true' : 'false'),
            'APP_URL' => $values['APP_URL'] ?? config('app.url', 'http://localhost'),
            'APP_TIMEZONE' => $values['APP_TIMEZONE'] ?? config('app.timezone', 'Europe/Istanbul'),
            'APP_LOCALE' => $values['APP_LOCALE'] ?? config('app.locale', 'en'),
            'APP_FALLBACK_LOCALE' => $values['APP_FALLBACK_LOCALE'] ?? config('app.fallback_locale', 'en'),
            'LOG_LEVEL' => $values['LOG_LEVEL'] ?? config('logging.level', 'debug'),
            'CACHE_STORE' => $values['CACHE_STORE'] ?? config('cache.default', 'database'),
            'QUEUE_CONNECTION' => $values['QUEUE_CONNECTION'] ?? config('queue.default', 'database'),
            'SESSION_DRIVER' => $values['SESSION_DRIVER'] ?? config('session.driver', 'database'),
            'SESSION_LIFETIME' => $values['SESSION_LIFETIME'] ?? config('session.lifetime', 120),
            'MAIL_MAILER' => $values['MAIL_MAILER'] ?? config('mail.default', 'log'),
            'MAIL_HOST' => $values['MAIL_HOST'] ?? config('mail.mailers.smtp.host', ''),
            'MAIL_PORT' => $values['MAIL_PORT'] ?? config('mail.mailers.smtp.port', ''),
            'MAIL_USERNAME' => $values['MAIL_USERNAME'] ?? config('mail.mailers.smtp.username', ''),
            'MAIL_FROM_ADDRESS' => $values['MAIL_FROM_ADDRESS'] ?? config('mail.from.address', ''),
            'MAIL_FROM_NAME' => $values['MAIL_FROM_NAME'] ?? config('mail.from.name', ''),
            'ENTRA_SYNC_ENABLED' => $values['ENTRA_SYNC_ENABLED'] ?? (config('services.microsoft_graph.sync_enabled') ? 'true' : 'false'),
            'ENTRA_TENANT_ID' => $values['ENTRA_TENANT_ID'] ?? config('services.microsoft_graph.tenant_id', ''),
            'ENTRA_CLIENT_ID' => $values['ENTRA_CLIENT_ID'] ?? config('services.microsoft_graph.client_id', ''),
            'ENTRA_CLIENT_SECRET' => '',
            'ENTRA_CLIENT_SECRET_SET' => filled($values['ENTRA_CLIENT_SECRET'] ?? config('services.microsoft_graph.client_secret')),
            'ENTRA_SYNC_GROUPS' => $values['ENTRA_SYNC_GROUPS'] ?? (config('services.microsoft_graph.sync_groups') ? 'true' : 'false'),
            'ENTRA_GROUP_PREFIX' => $values['ENTRA_GROUP_PREFIX'] ?? config('services.microsoft_graph.group_prefix', ''),
        ];
    }

    private function parseEnvFile(): array
    {
        $path = base_path('.env');
        if (! File::exists($path)) {
            return [];
        }

        $values = [];
        foreach (preg_split('/\r\n|\r|\n/', (string) File::get($path)) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = $this->unquoteEnvValue(trim($value));
        }

        return $values;
    }

    private function writeEnvValues(array $values): void
    {
        $path = base_path('.env');
        $content = File::exists($path) ? (string) File::get($path) : '';
        if (File::exists($path)) {
            $backupDir = storage_path('app/env-backups');
            File::ensureDirectoryExists($backupDir);
            File::copy($path, $backupDir.'/env-'.now()->format('Ymd-His').'.backup');
        }

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->formatEnvValue((string) $value);
            if (preg_match('/^'.preg_quote($key, '/').'=.*/m', $content)) {
                $content = preg_replace('/^'.preg_quote($key, '/').'=.*/m', $line, $content);
            } else {
                $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
            }
        }

        File::put($path, rtrim($content).PHP_EOL);
    }

    private function unquoteEnvValue(string $value): string
    {
        if (strlen($value) >= 2 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            return str_replace(['\\"', '\\\\'], ['"', '\\'], substr($value, 1, -1));
        }

        return $value;
    }

    private function formatEnvValue(string $value): string
    {
        if ($value === 'true' || $value === 'false' || is_numeric($value)) {
            return $value;
        }

        if ($value === '' || preg_match('/\s|#|"|\\\\/', $value)) {
            return '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"';
        }

        return $value;
    }
}
