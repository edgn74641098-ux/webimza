<?php

use App\Services\MicrosoftGraph\GraphDirectorySyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('entra:sync', function (GraphDirectorySyncService $syncService) {
    $summary = $syncService->sync();

    $this->info('Microsoft 365 senkronizasyonu tamamlandi.');
    $this->table(['Metrik', 'Adet'], collect($summary)->map(
        fn ($value, $key) => [$key, $value]
    )->all());
})->purpose('Microsoft 365 / Entra ID kullanici, departman ve grup verilerini senkronize eder');
