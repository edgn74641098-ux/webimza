<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddinDevice;
use App\Models\User;
use Illuminate\Http\Request;

class HeartbeatController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->validate([
            'email' => ['required', 'email'],
            'deviceId' => ['required', 'string'],
            'addinVersion' => ['nullable', 'string'],
            'clientType' => ['nullable', 'string'],
            'officeVersion' => ['nullable', 'string'],
        ]);

        $user = User::where('email', $payload['email'])->first();

        AddinDevice::updateOrCreate(['email' => $payload['email']], [
            'user_id' => $user?->id,
            'device_id' => $payload['deviceId'],
            'email' => $payload['email'],
            'client_type' => $payload['clientType'] ?? 'unknown',
            'office_version' => $payload['officeVersion'] ?? null,
            'addin_version' => $payload['addinVersion'] ?? null,
            'last_seen_at' => now(),
            'status' => 'active',
        ]);

        return response()->json(['success' => true]);
    }
}
