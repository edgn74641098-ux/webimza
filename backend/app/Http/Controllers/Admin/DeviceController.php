<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddinDevice;
use App\Models\AddinLog;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = AddinDevice::with('user');

        if ($request->filled('addin_version')) {
            $query->where('addin_version', $request->string('addin_version'));
        }

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->string('client_type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('last_seen_from')) {
            $query->whereDate('last_seen_at', '>=', $request->string('last_seen_from'));
        }

        $devices = $query->latest('last_seen_at')->paginate(25)->withQueryString();

        return view('admin.devices.index', [
            'devices' => $devices,
            'filters' => $request->only(['client_type', 'addin_version', 'status', 'last_seen_from']),
            'clientTypes' => AddinDevice::query()->select('client_type')->distinct()->pluck('client_type'),
            'addinVersions' => AddinDevice::query()->select('addin_version')->whereNotNull('addin_version')->distinct()->pluck('addin_version'),
            'statuses' => AddinDevice::query()->select('status')->whereNotNull('status')->distinct()->pluck('status'),
        ]);
    }

    public function show(AddinDevice $device)
    {
        $logs = AddinLog::query()
            ->with('user')
            ->where('device_id', $device->device_id)
            ->latest()
            ->limit(20)
            ->get();
        $lastHeartbeat = $logs->firstWhere('event_type', 'heartbeat');
        $compatibility = in_array($device->client_type, ['classic_outlook', 'new_outlook', 'outlook_web'], true)
            ? 'supported'
            : 'unsupported';

        return view('admin.devices.show', [
            'device' => $device->load('user'),
            'logs' => $logs,
            'lastHeartbeat' => $lastHeartbeat,
            'compatibility' => $compatibility,
        ]);
    }
}

