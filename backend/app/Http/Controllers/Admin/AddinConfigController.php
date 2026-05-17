<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddinBuild;
use App\Models\AddinConfig;
use App\Services\AddinBuild\AddinBuildService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddinConfigController extends Controller
{
    public function index()
    {
        $config = AddinConfig::first();

        if (! $config) {
            $baseUrl = rtrim((string) config('app.url', 'http://127.0.0.1:8000'), '/');
            $config = AddinConfig::create([
                'name' => 'default',
                'manifest_id' => (string) Str::uuid(),
                'api_base_url' => $baseUrl.'/api',
                'manifest_version' => '1.1.0.0',
                'provider_name' => 'TRINOX',
                'display_name' => 'TRINOX Signature Manager',
                'support_url' => $baseUrl,
                'taskpane_url' => $baseUrl.'/addin/index.html',
                'autorun_url' => $baseUrl.'/addin/autorun.html',
                'icon_url' => $baseUrl.'/addin/icon-32.png',
                'highres_icon_url' => $baseUrl.'/addin/icon-80.png',
            ]);
        }

        if (empty($config->manifest_id)) {
            $config->manifest_id = (string) Str::uuid();
            $config->save();
        }

        return view('admin.deployment.index', [
            'config' => $config,
            'builds' => AddinBuild::latest()->limit(20)->get(),
            'latestBuild' => AddinBuild::latest()->first(),
            'validationChecklist' => $this->buildValidationChecklist($config),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'api_base_url' => ['required', 'string'],
            'manifest_id' => ['nullable', 'string'],
            'manifest_version' => ['required', 'string'],
            'provider_name' => ['required', 'string'],
            'display_name' => ['required', 'string'],
            'support_url' => ['nullable', 'string'],
            'taskpane_url' => ['required', 'string'],
            'autorun_url' => ['required', 'string'],
            'icon_url' => ['nullable', 'string'],
            'highres_icon_url' => ['nullable', 'string'],
            'allowed_domains' => ['nullable', 'string'],
        ]);

        $config = AddinConfig::firstOrFail();
        $data['updated_by'] = auth()->id();
        $config->update($data);

        return redirect()->route('admin.deployment.index')->with('success', 'Add-in ayarlari guncellendi.');
    }

    public function build(AddinBuildService $builder)
    {
        try {
            $config = AddinConfig::firstOrFail();
            $buildType = request()->input('build_type', 'manual_basic');
            $build = $builder->build($config, (int) auth()->id(), (string) $buildType);

            return redirect()->route('admin.deployment.index')->with('success', 'Build hazirlandi: #'.$build->id);
        } catch (\Throwable $e) {
            return redirect()->route('admin.deployment.index')->with('error', 'Build basarisiz: '.$e->getMessage());
        }
    }

    public function downloadManifest(AddinBuild $build)
    {
        abort_unless($build->manifest_path && file_exists($build->manifest_path), 404);

        return response()->download($build->manifest_path, 'manifest.xml');
    }

    public function downloadPackage(AddinBuild $build)
    {
        abort_unless($build->package_path && file_exists($build->package_path), 404);

        return response()->download($build->package_path, 'outlook-addin.zip');
    }

    public function downloadValidationReport()
    {
        $config = AddinConfig::firstOrFail();
        $checklist = $this->buildValidationChecklist($config);

        $lines = [
            'TRINOX Add-in Validation Report',
            'Generated At: '.now()->toDateTimeString(),
            str_repeat('-', 60),
        ];

        foreach ($checklist as $item) {
            $lines[] = sprintf(
                '[%s] %s | %s',
                $item['ok'] ? 'OK' : 'FAIL',
                $item['label'],
                $item['detail'] ?? '-'
            );
        }

        $content = implode(PHP_EOL, $lines).PHP_EOL;

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="validation-report.txt"',
        ]);
    }

    public function downloadDeploymentGuide()
    {
        $content = implode(PHP_EOL, [
            '# TRINOX Microsoft 365 Deployment Guide',
            '',
            '1. Microsoft 365 Admin Center ac.',
            '2. Settings > Integrated apps.',
            '3. Upload custom app.',
            '4. Manifest yukle.',
            '5. Test grubuna ata.',
            '6. Deploy.',
            '7. Outlook Webde test et.',
            '8. Klasik Outlookta test et.',
            '',
            'Notlar:',
            '- Sistem push degildir; compose/check akisinda etkilesir.',
            '- Production icin HTTPS endpointler zorunludur.',
            '- Rollout sonrasi device ve log ekranlarindan takip yapin.',
            '',
            'Generated At: '.now()->toDateTimeString(),
        ]).PHP_EOL;

        return response($content, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="m365-deployment-guide.md"',
        ]);
    }

    private function buildValidationChecklist(AddinConfig $config): array
    {
        $apiFilled = ! empty($config->api_base_url);
        $apiHttps = $this->isHttpsUrl($config->api_base_url);
        $apiPathLooksRight = $this->apiBaseLooksValid($config->api_base_url);
        $taskpaneHttps = $this->isHttpsUrl($config->taskpane_url);
        $iconsExist = ! empty($config->icon_url);
        $manifestGuid = $this->isGuid((string) $config->manifest_id);
        $versionFormat = $this->isVersionFormat((string) $config->manifest_version);

        return [
            ['label' => 'API URL dolu mu?', 'ok' => $apiFilled, 'detail' => $config->api_base_url ?: '-'],
            ['label' => 'API URL HTTPS mi?', 'ok' => $apiHttps, 'detail' => $config->api_base_url ?: '-'],
            ['label' => 'API URL /api ile bitiyor mu?', 'ok' => $apiPathLooksRight, 'detail' => $config->api_base_url ?: '-'],
            ['label' => 'Taskpane URL HTTPS mi?', 'ok' => $taskpaneHttps, 'detail' => $config->taskpane_url ?: '-'],
            ['label' => 'Manifest ID GUID mi?', 'ok' => $manifestGuid, 'detail' => $config->manifest_id ?: '-'],
            ['label' => 'Version format dogru mu?', 'ok' => $versionFormat, 'detail' => $config->manifest_version ?: '-'],
            ['label' => 'Icon URL var mi?', 'ok' => $iconsExist, 'detail' => $config->icon_url ?: '-'],
        ];
    }

    private function isHttpsUrl(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        return str_starts_with(strtolower($url), 'https://');
    }

    private function isGuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[1-5][0-9a-fA-F]{3}\-[89abAB][0-9a-fA-F]{3}\-[0-9a-fA-F]{12}$/',
            $value
        );
    }

    private function isVersionFormat(string $value): bool
    {
        return (bool) preg_match('/^\d+\.\d+\.\d+\.\d+$/', $value);
    }

    private function apiBaseLooksValid(?string $url): bool
    {
        if (! $url) {
            return false;
        }

        return str_ends_with(rtrim(strtolower($url), '/'), '/api');
    }

}


