<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddinLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = AddinLog::with(['user', 'device']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->string('event_type'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->integer('user_id'));
        }

        if ($request->filled('date')) {
            $date = Carbon::parse($request->string('date'));
            $query->whereBetween('created_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()]);
        }
        
        if ($request->filled('q')) {
            $q = trim((string) $request->string('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('message', 'like', "%{$q}%")
                    ->orWhere('event_type', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('device_id', 'like', "%{$q}%")
                    ->orWhereHas('user', function ($userQuery) use ($q) {
                        $userQuery->where('email', 'like', "%{$q}%")
                            ->orWhere('name', 'like', "%{$q}%");
                    });
            });
        }

        $logs = $query->latest()->paginate(30)->withQueryString();
        $logs->getCollection()->transform(function (AddinLog $log) {
            $payload = $log->payload_data;

            $log->duration_value = $payload['duration_ms']
                ?? $payload['duration']
                ?? $payload['latency_ms']
                ?? null;
            $log->request_id_value = $payload['request_id']
                ?? $payload['requestId']
                ?? null;
            $log->signature_version_value = $payload['signature_version']
                ?? $payload['signatureVersion']
                ?? null;
            $log->error_code_value = $payload['error_code']
                ?? $payload['code']
                ?? null;
            $log->client_value = $payload['client_type']
                ?? $payload['client']
                ?? $log->device?->client_type
                ?? 'unknown';

            return $log;
        });

        return view('admin.logs.index', [
            'logs' => $logs,
            'filters' => $request->only([
                'status',
                'event_type',
                'user_id',
                'date',
                'q',
            ]),
            'eventTypes' => AddinLog::query()->select('event_type')->distinct()->orderBy('event_type')->pluck('event_type'),
            'statuses' => AddinLog::query()->select('status')->distinct()->orderBy('status')->pluck('status'),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function show(AddinLog $log)
    {
        $log->load(['user', 'device']);
        $payload = $log->payload_data;

        return view('admin.logs.show', [
            'log' => $log,
            'prettyPayload' => json_encode($payload ?: new \stdClass(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'requestId' => $payload['request_id'] ?? $payload['requestId'] ?? '-',
            'errorCode' => $payload['error_code'] ?? $payload['code'] ?? '-',
        ]);
    }
}
