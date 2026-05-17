<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddinDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AddinRegisterController extends Controller
{
    public function __invoke(Request $request)
    {
        $payload = $request->validate([
            'email' => ['required', 'email'],
            'displayName' => ['nullable', 'string'],
            'username' => ['nullable', 'string'],
            'title' => ['nullable', 'string'],
            'department' => ['nullable', 'string'],
            'company' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'mobile' => ['nullable', 'string'],
            'office' => ['nullable', 'string'],
            'website' => ['nullable', 'string'],
            'clientType' => ['nullable', 'string'],
            'platform' => ['nullable', 'string'],
            'host' => ['nullable', 'string'],
            'officeVersion' => ['nullable', 'string'],
            'addinVersion' => ['nullable', 'string'],
            'deviceId' => ['nullable', 'string'],
        ]);

        $email = $payload['email'];
        $fallbackName = Str::before($email, '@');
        $fallbackUsername = $payload['username'] ?? $fallbackName;
        $user = User::firstOrCreate(['email' => $email], [
            'name' => $payload['displayName'] ?? $fallbackName,
            'username' => $fallbackUsername,
            'domain' => Str::after($email, '@'),
            'title' => $payload['title'] ?? null,
            'company' => $payload['company'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'mobile' => $payload['mobile'] ?? null,
            'office' => $payload['office'] ?? null,
            'website' => $payload['website'] ?? null,
            // users.source enum does not include "addin"; classify add-in discovered users as manual.
            'source' => 'manual',
            'is_active' => true,
        ]);

        // Keep user profile fresh from Office/Outlook identity data when fields are blank.
        $user->fill([
            'name' => $user->name ?: ($payload['displayName'] ?? $fallbackName),
            'username' => $user->username ?: $fallbackUsername,
            'domain' => $user->domain ?: Str::after($email, '@'),
            'title' => $user->title ?: ($payload['title'] ?? null),
            'company' => $user->company ?: ($payload['company'] ?? null),
            'phone' => $user->phone ?: ($payload['phone'] ?? null),
            'mobile' => $user->mobile ?: ($payload['mobile'] ?? null),
            'office' => $user->office ?: ($payload['office'] ?? null),
            'website' => $user->website ?: ($payload['website'] ?? null),
            'source' => $user->source ?: 'manual',
        ]);
        $user->save();

        $deviceId = $payload['deviceId'] ?? (string) Str::uuid();

        // Keep exactly one device row per user email in admin panel.
        AddinDevice::updateOrCreate(['email' => $payload['email']], [
            'user_id' => $user->id,
            'device_id' => $deviceId,
            'email' => $payload['email'],
            'display_name' => $payload['displayName'] ?? null,
            'client_type' => $payload['clientType'] ?? 'unknown',
            'platform' => $payload['platform'] ?? null,
            'host' => $payload['host'] ?? null,
            'office_version' => $payload['officeVersion'] ?? null,
            'addin_version' => $payload['addinVersion'] ?? null,
            'last_ip' => $request->ip(),
            'last_seen_at' => now(),
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'userId' => $user->id,
            'deviceId' => $deviceId,
            'serverTime' => now()->toIso8601String(),
        ]);
    }
}
