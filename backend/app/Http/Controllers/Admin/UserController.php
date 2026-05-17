<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddinDevice;
use App\Models\AddinLog;
use App\Models\Department;
use App\Models\User;
use App\Services\SignatureAssignmentService;
use App\Services\SignatureRenderService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with('department')
            ->withCount('addinDevices')
            ->withMax('addinDevices', 'last_seen_at');

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        $query->latest();

        $users = $query->paginate(15)->withQueryString();
        $userIds = $users->getCollection()->pluck('id');

        $latestDeviceRows = AddinDevice::query()
            ->select('user_id', 'last_signature_version')
            ->whereIn('user_id', $userIds)
            ->whereNotNull('last_signature_version')
            ->orderByDesc('last_seen_at')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $users->getCollection()->transform(function (User $user) use ($latestDeviceRows) {
            $user->last_signature_version = $latestDeviceRows[$user->id]->last_signature_version ?? null;

            return $user;
        });

        return view('admin.users.index', [
            'users' => $users,
            'departments' => Department::orderBy('name')->get(),
            'filters' => $request->only(['q', 'status', 'department_id']),
        ]);
    }

    public function show(
        User $user,
        SignatureAssignmentService $assignmentService,
        SignatureRenderService $renderService
    )
    {
        $user->load(['department', 'groups', 'addinDevices']);

        $resolvedTemplate = $assignmentService->resolveForUser($user);
        $preview = $resolvedTemplate ? $renderService->render($resolvedTemplate, $user) : null;

        $resolvedFrom = $this->resolveAssignmentSource($user, $resolvedTemplate?->id);

        return view('admin.users.show', [
            'user' => $user,
            'recentLogs' => AddinLog::query()
                ->where('user_id', $user->id)
                ->latest()
                ->limit(50)
                ->get(),
            'resolvedTemplate' => $resolvedTemplate,
            'resolvedFrom' => $resolvedFrom,
            'preview' => $preview,
        ]);
    }

    public function create()
    {
        return view('admin.users.create', ['departments' => Department::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['source'] = 'manual';
        $data['domain'] = str_contains($data['email'], '@') ? explode('@', $data['email'])[1] : null;
        $data['password'] = bcrypt('password');
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'Kullanici olusturuldu.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', ['user' => $user, 'departments' => Department::orderBy('name')->get()]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'department_id' => ['nullable', 'exists:departments,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['domain'] = str_contains($data['email'], '@') ? explode('@', $data['email'])[1] : null;
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Kullanici guncellendi.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Kullanici silindi.');
    }

    private function resolveAssignmentSource(User $user, ?int $templateId): ?string
    {
        if (! $templateId) {
            return null;
        }

        $hasUserRule = $user->signatureAssignments()
            ->where('assignment_type', 'user')
            ->where('template_id', $templateId)
            ->where('is_active', true)
            ->exists();
        if ($hasUserRule) {
            return 'user';
        }

        $groupIds = $user->groups->pluck('id');
        if ($groupIds->isNotEmpty()) {
            $hasGroupRule = \App\Models\SignatureAssignment::query()
                ->where('assignment_type', 'group')
                ->whereIn('group_id', $groupIds)
                ->where('template_id', $templateId)
                ->where('is_active', true)
                ->exists();
            if ($hasGroupRule) {
                return 'group';
            }
        }

        if ($user->department_id) {
            $hasDepartmentRule = \App\Models\SignatureAssignment::query()
                ->where('assignment_type', 'department')
                ->where('department_id', $user->department_id)
                ->where('template_id', $templateId)
                ->where('is_active', true)
                ->exists();
            if ($hasDepartmentRule) {
                return 'department';
            }
        }

        return 'default';
    }
}
