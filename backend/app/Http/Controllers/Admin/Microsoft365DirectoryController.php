<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MicrosoftGraph\GraphDirectorySyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class Microsoft365DirectoryController extends Controller
{
    private const SCOPES = [
        'openid',
        'profile',
        'https://graph.microsoft.com/User.Read',
        'https://graph.microsoft.com/User.Read.All',
        'https://graph.microsoft.com/GroupMember.Read.All',
    ];

    public function connect(Request $request)
    {
        if (! $this->publicClientConfigured()) {
            return redirect()
                ->route('admin.settings.index')
                ->withErrors(['microsoft365' => 'Microsoft 365 modern auth baslatmak icin Azure App Registration Client ID gereklidir.']);
        }

        $verifier = Str::random(96);
        $state = Str::random(48);

        $request->session()->put('graph_oauth_state', $state);
        $request->session()->put('graph_oauth_verifier', $verifier);

        $query = http_build_query([
            'client_id' => config('services.microsoft_graph.client_id'),
            'response_type' => 'code',
            'redirect_uri' => route('admin.microsoft365.callback'),
            'response_mode' => 'query',
            'scope' => implode(' ', self::SCOPES),
            'state' => $state,
            'code_challenge' => $this->codeChallenge($verifier),
            'code_challenge_method' => 'S256',
            'prompt' => 'select_account',
        ]);

        $authorizeUrl = $this->authorityUrl().'/oauth2/v2.0/authorize?'.$query;
        Log::info('Microsoft 365 delegated auth redirect prepared.', [
            'tenant' => config('services.microsoft_graph.tenant_id'),
            'client_id' => config('services.microsoft_graph.client_id'),
            'redirect_uri' => route('admin.microsoft365.callback'),
        ]);

        return redirect()->away($authorizeUrl);
    }

    public function callback(Request $request)
    {
        if ($request->filled('error')) {
            return redirect()
                ->route('admin.settings.index')
                ->withErrors(['microsoft365' => $request->string('error_description')->toString() ?: $request->string('error')->toString()]);
        }

        if (! hash_equals((string) $request->session()->pull('graph_oauth_state'), (string) $request->query('state'))) {
            return redirect()
                ->route('admin.settings.index')
                ->withErrors(['microsoft365' => 'Microsoft oturum dogrulamasi gecersiz state ile dondu. Lutfen tekrar deneyin.']);
        }

        $verifier = $request->session()->pull('graph_oauth_verifier');
        if (blank($verifier) || blank($request->query('code'))) {
            return redirect()
                ->route('admin.settings.index')
                ->withErrors(['microsoft365' => 'Microsoft oturum kodu alinamadi. Lutfen tekrar deneyin.']);
        }

        $response = Http::asForm()
            ->timeout(30)
            ->post($this->authorityUrl().'/oauth2/v2.0/token', [
                'client_id' => config('services.microsoft_graph.client_id'),
                'grant_type' => 'authorization_code',
                'code' => $request->query('code'),
                'redirect_uri' => route('admin.microsoft365.callback'),
                'code_verifier' => $verifier,
                'scope' => implode(' ', self::SCOPES),
            ]);

        if (! $response->successful()) {
            return redirect()
                ->route('admin.settings.index')
                ->withErrors(['microsoft365' => 'Microsoft token alinamadi: '.$response->body()]);
        }

        $token = $response->json('access_token');
        if (blank($token)) {
            return redirect()
                ->route('admin.settings.index')
                ->withErrors(['microsoft365' => 'Microsoft token yanitinda access_token yok.']);
        }

        $request->session()->put('graph_access_token', $token);
        $request->session()->put('graph_expires_at', now()->addSeconds((int) $response->json('expires_in', 3600) - 60)->timestamp);

        return redirect()->route('admin.microsoft365.directory');
    }

    public function directory(Request $request, GraphDirectorySyncService $syncService)
    {
        $token = $this->sessionToken($request);
        if (! $token) {
            return redirect()->route('admin.settings.index')->withErrors([
                'microsoft365' => 'Once Microsoft 365 modern auth ile baglanin.',
            ]);
        }

        try {
            $directory = $syncService->preview($token);
        } catch (RuntimeException $exception) {
            $request->session()->forget(['graph_access_token', 'graph_expires_at']);

            return redirect()->route('admin.settings.index')->withErrors([
                'microsoft365' => $exception->getMessage(),
            ]);
        }

        return view('admin.microsoft365.directory', [
            'users' => $directory['users'],
            'groups' => $directory['groups'],
            'expiresAt' => $request->session()->get('graph_expires_at'),
        ]);
    }

    public function import(Request $request, GraphDirectorySyncService $syncService)
    {
        $token = $this->sessionToken($request);
        if (! $token) {
            return redirect()->route('admin.settings.index')->withErrors([
                'microsoft365' => 'Oturum suresi doldu. Microsoft 365 ile tekrar baglanin.',
            ]);
        }

        $data = $request->validate([
            'users' => ['array'],
            'users.*' => ['string'],
            'groups' => ['array'],
            'groups.*' => ['string'],
        ]);

        $summary = $syncService->importSelected($token, $data['users'] ?? [], $data['groups'] ?? []);

        return redirect()
            ->route('admin.microsoft365.directory')
            ->with('success', sprintf(
                'Import tamamlandi. Kullanici: %d yeni / %d guncel / %d atlandi, departman: %d, grup: %d yeni / %d guncel, uyelik: %d.',
                $summary['users_created'],
                $summary['users_updated'],
                $summary['users_skipped'],
                $summary['departments_created'],
                $summary['groups_created'],
                $summary['groups_updated'],
                $summary['memberships_synced'],
            ));
    }

    public function disconnect(Request $request)
    {
        $request->session()->forget(['graph_access_token', 'graph_expires_at']);

        return redirect()->route('admin.settings.index')->with('success', 'Microsoft 365 gecici oturumu kapatildi.');
    }

    private function publicClientConfigured(): bool
    {
        return filled(config('services.microsoft_graph.client_id'));
    }

    private function authorityUrl(): string
    {
        $tenant = trim((string) config('services.microsoft_graph.tenant_id', 'organizations'));

        return 'https://login.microsoftonline.com/'.($tenant !== '' ? $tenant : 'organizations');
    }

    private function sessionToken(Request $request): ?string
    {
        $expiresAt = (int) $request->session()->get('graph_expires_at', 0);
        if ($expiresAt <= now()->timestamp) {
            $request->session()->forget(['graph_access_token', 'graph_expires_at']);

            return null;
        }

        return $request->session()->get('graph_access_token');
    }

    private function codeChallenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }
}
