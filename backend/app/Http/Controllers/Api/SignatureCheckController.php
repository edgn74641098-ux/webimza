<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AddinDevice;
use App\Models\ForceUpdate;
use App\Models\User;
use App\Services\SignatureAssignmentService;
use App\Services\SignatureRenderService;
use Illuminate\Http\Request;

class SignatureCheckController extends Controller
{
    public function __invoke(Request $request, SignatureAssignmentService $assignmentService, SignatureRenderService $renderService)
    {
        $payload = $request->validate([
            'email' => ['required', 'email'],
            'deviceId' => ['required', 'string'],
            'currentSignatureVersion' => ['nullable', 'string'],
            'lastCheckAt' => ['nullable', 'date'],
        ]);

        $user = User::where('email', $payload['email'])->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'state' => 'user_not_found',
                'message' => 'User not found',
                'updateRequired' => false,
                'forceUpdate' => false,
                'signatureVersion' => null,
                'signatureName' => null,
                'html' => '',
                'text' => '',
                'cacheSeconds' => 300,
            ]);
        }

        $template = $assignmentService->resolveForUser($user);
        if (! $template) {
            return response()->json([
                'success' => false,
                'state' => 'no_template_assigned',
                'message' => 'Template not found',
                'updateRequired' => false,
                'forceUpdate' => false,
                'signatureVersion' => null,
                'signatureName' => null,
                'html' => '',
                'text' => '',
                'cacheSeconds' => 300,
            ]);
        }

        $forceUpdate = ForceUpdate::query()
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) use ($user, $template) {
                $groupIds = $user->groups()->pluck('groups.id')->all();

                $q->where('scope_key', 'all')
                    ->orWhere(fn ($qq) => $qq->where('scope_key', 'user')->where('user_id', $user->id))
                    ->orWhere(fn ($qq) => $qq->where('scope_key', 'department')->where('department_id', $user->department_id))
                    ->orWhere(fn ($qq) => $qq->where('scope_key', 'group')->whereIn('group_id', $groupIds))
                    ->orWhere(fn ($qq) => $qq->where('scope_key', 'template')->where('template_id', $template->id))
                    ->orWhere(fn ($qq) => $qq->whereNull('scope_key')->where('scope_type', 'all'))
                    ->orWhere(fn ($qq) => $qq->whereNull('scope_key')->where('scope_type', 'user')->where('user_id', $user->id))
                    ->orWhere(fn ($qq) => $qq->whereNull('scope_key')->where('scope_type', 'department')->where('department_id', $user->department_id));
            })
            ->exists();

        $updateRequired = $forceUpdate || ($payload['currentSignatureVersion'] ?? null) !== $template->version;

        AddinDevice::where('device_id', $payload['deviceId'])->update([
            'last_check_at' => now(),
            'last_seen_at' => now(),
            'last_signature_version' => $template->version,
        ]);

        $rendered = $renderService->render($template, $user);

        return response()->json([
            'success' => true,
            'updateRequired' => $updateRequired,
            'forceUpdate' => $forceUpdate,
            'signatureVersion' => $template->version,
            'signatureName' => $template->name,
            'html' => $rendered['html'],
            'text' => $rendered['text'],
            'cacheSeconds' => 86400,
        ]);
    }
}
