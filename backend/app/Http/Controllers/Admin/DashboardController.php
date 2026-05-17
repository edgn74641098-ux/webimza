<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddinDevice;
use App\Models\AddinLog;
use App\Models\SignatureTemplate;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $latestTemplateVersion = SignatureTemplate::where('is_active', true)->latest()->value('version');

        $last24hErrors = AddinLog::query()
            ->where('status', 'error')
            ->where('created_at', '>=', now()->subDay())
            ->count();

        $unsupportedClients = AddinDevice::query()
            ->where(function ($q) {
                $q->whereNull('client_type')
                    ->orWhere('client_type', 'unknown')
                    ->orWhereNotIn('client_type', ['classic_outlook', 'new_outlook', 'outlook_web']);
            })
            ->count();

        $recentActivities = AddinLog::query()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();

        $recentErrors = AddinLog::query()
            ->with('user')
            ->where('status', 'error')
            ->latest()
            ->limit(10)
            ->get();

        $updatedUsers = $latestTemplateVersion
            ? AddinDevice::query()->where('last_signature_version', $latestTemplateVersion)->distinct('email')->count('email')
            : 0;

        $pendingUsers = max(User::count() - $updatedUsers, 0);

        return view('admin.dashboard', [
            'kpis' => [
                'total_users' => User::count(),
                'active_devices' => AddinDevice::count(),
                'up_to_date_users' => $updatedUsers,
                'pending_update_users' => $pendingUsers,
                'errors_24h' => $last24hErrors,
                'unsupported_clients' => $unsupportedClients,
            ],
            'recentActivities' => $recentActivities,
            'recentErrors' => $recentErrors,
            'latestTemplateVersion' => $latestTemplateVersion,
        ]);
    }
}
