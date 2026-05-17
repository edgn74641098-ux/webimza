<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AddinDevice;
use App\Models\Department;
use App\Models\ForceUpdate;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ForceUpdateController extends Controller
{
    public function index()
    {
        $users = User::with(['groups', 'department', 'addinDevices'])->orderBy('name')->get();
        $updates = ForceUpdate::query()->with(['creator', 'user', 'department', 'group'])->latest()->paginate(20);

        $updates->getCollection()->transform(function (ForceUpdate $update) {
            $scopedUsersQuery = $this->scopedUsersQuery($update);
            $affectedUsers = (int) ($update->affected_users ?? $scopedUsersQuery->count());

            $completedUsers = 0;
            if ($affectedUsers > 0) {
                if ($update->target_version) {
                    $scopedUserIds = $scopedUsersQuery->pluck('id');
                    $completedUsers = AddinDevice::query()
                        ->whereIn('user_id', $scopedUserIds)
                        ->where('last_signature_version', $update->target_version)
                        ->distinct('user_id')
                        ->count('user_id');
                } elseif ($update->status === 'completed') {
                    $completedUsers = $affectedUsers;
                }
            }

            $pendingUsers = max($affectedUsers - $completedUsers, 0);
            $progress = $affectedUsers > 0 ? (int) round(($completedUsers / $affectedUsers) * 100) : 0;
            $computedStatus = $update->status;

            if ($update->status !== 'cancelled') {
                if ($update->expires_at && now()->greaterThan($update->expires_at) && $completedUsers < $affectedUsers) {
                    $computedStatus = 'expired';
                } elseif ($affectedUsers > 0 && $completedUsers >= $affectedUsers) {
                    $computedStatus = 'completed';
                } elseif ($completedUsers > 0 && $completedUsers < $affectedUsers) {
                    $computedStatus = 'partially_completed';
                } else {
                    $computedStatus = 'pending';
                }
            }

            $update->computed_scope = $update->scope_key ?? $update->scope_type;
            $update->computed_target = $this->scopeTargetLabel($update);
            $update->computed_affected_users = $affectedUsers;
            $update->computed_completed_users = $completedUsers;
            $update->computed_pending_users = $pendingUsers;
            $update->computed_progress = $progress;
            $update->computed_status = $computedStatus;

            return $update;
        });

        return view('admin.updates.index', [
            'updates' => $updates,
            'users' => $users,
            'departments' => Department::orderBy('name')->get(),
            'groups' => Group::orderBy('name')->get(),
            'resultId' => request('result_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'scope_key' => ['required', 'in:all,department,group,user'],
            'user_id' => ['nullable', 'exists:users,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'reason' => ['required', 'string', 'min:5'],
            'ttl_days' => ['required', 'in:1,3,7'],
        ]);

        $users = User::with(['groups', 'addinDevices'])->get();

        $targetUsers = $users->filter(function ($user) use ($data) {
            return match ($data['scope_key']) {
                'all' => true,
                'department' => (int) $user->department_id === (int) ($data['department_id'] ?? 0),
                'group' => $user->groups->contains('id', (int) ($data['group_id'] ?? 0)),
                'user' => (int) $user->id === (int) ($data['user_id'] ?? 0),
                default => false,
            };
        })->values();

        $affectedUsers = $targetUsers->count();
        $affectedDevices = $targetUsers->sum(fn ($u) => $u->addinDevices->count());

        $scopeType = match ($data['scope_key']) {
            'user' => 'user',
            'department' => 'department',
            default => 'all',
        };

        $forceUpdate = ForceUpdate::create([
            'scope_type' => $scopeType,
            'scope_key' => $data['scope_key'],
            'user_id' => $data['scope_key'] === 'user' ? $data['user_id'] : null,
            'department_id' => $data['scope_key'] === 'department' ? $data['department_id'] : null,
            'group_id' => $data['scope_key'] === 'group' ? $data['group_id'] : null,
            'template_id' => null,
            'target_version' => null,
            'status' => 'pending',
            'reason' => $data['reason'],
            'ttl_days' => (int) $data['ttl_days'],
            'expires_at' => now()->addDays((int) $data['ttl_days']),
            'affected_users' => $affectedUsers,
            'affected_devices' => $affectedDevices,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('admin.updates.index', ['result_id' => $forceUpdate->id])
            ->with('success', 'Force update kaydi olusturuldu.');
    }

    public function show(ForceUpdate $forceUpdate)
    {
        $scopedUsersQuery = $this->scopedUsersQuery($forceUpdate);
        $affectedUsers = (int) ($forceUpdate->affected_users ?? $scopedUsersQuery->count());
        $completedUsers = $forceUpdate->status === 'completed' ? $affectedUsers : 0;
        $pendingUsers = max($affectedUsers - $completedUsers, 0);
        $progress = $affectedUsers > 0 ? (int) round(($completedUsers / $affectedUsers) * 100) : 0;

        return view('admin.updates.show', [
            'update' => $forceUpdate->load(['creator', 'user', 'department', 'group']),
            'scopeLabel' => $forceUpdate->scope_key ?? $forceUpdate->scope_type,
            'targetLabel' => $this->scopeTargetLabel($forceUpdate),
            'affectedUsers' => $affectedUsers,
            'completedUsers' => $completedUsers,
            'pendingUsers' => $pendingUsers,
            'progress' => $progress,
        ]);
    }

    private function scopedUsersQuery(ForceUpdate $update): Builder
    {
        $scope = $update->scope_key ?? $update->scope_type;

        return match ($scope) {
            'user' => User::query()->whereKey($update->user_id),
            'department' => User::query()->where('department_id', $update->department_id),
            'group' => User::query()->whereHas('groups', fn ($q) => $q->where('groups.id', $update->group_id)),
            default => User::query(),
        };
    }

    private function scopeTargetLabel(ForceUpdate $update): string
    {
        $scope = $update->scope_key ?? $update->scope_type;

        return match ($scope) {
            'user' => $update->user?->email ?? ('Kullanici #'.$update->user_id),
            'department' => $update->department?->name ?? ('Departman #'.$update->department_id),
            'group' => $update->group?->name ?? ('Grup #'.$update->group_id),
            default => 'Tum kullanicilar',
        };
    }
}


