<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddinBuild;
use App\Models\AddinConfig;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
        ]);
    }
}
