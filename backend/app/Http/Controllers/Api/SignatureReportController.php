<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddinDevice;
use App\Models\AddinLog;
use App\Models\User;
use Illuminate\Http\Request;

class SignatureReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->validate([
            'email' => ['required', 'email'],
            'deviceId' => ['required', 'string'],
            'signatureVersion' => ['nullable', 'string'],
            'status' => ['required', 'string'],
            'message' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $payload['email'])->first();

        AddinLog::create([
            'user_id' => $user?->id,
            'device_id' => $payload['deviceId'],
            'event_type' => $payload['status'] === 'success' ? 'signature_applied' : 'signature_failed',
            'status' => $payload['status'],
            'message' => $payload['message'] ?? null,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'ip_address' => $request->ip(),
        ]);

        AddinDevice::where('device_id', $payload['deviceId'])->update([
            'last_signature_version' => $payload['signatureVersion'] ?? null,
            'last_seen_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
