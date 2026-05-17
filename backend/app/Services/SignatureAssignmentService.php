<?php

namespace App\Services;

use App\Models\SignatureAssignment;
use App\Models\SignatureTemplate;
use App\Models\User;

class SignatureAssignmentService
{
    public function resolveForUser(User $user): ?SignatureTemplate
    {
        $groupIds = $user->groups()->pluck('groups.id')->all();

        $assignment = SignatureAssignment::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            })
            ->where(function ($query) use ($user, $groupIds) {
                $query->where(fn ($q) => $q->where('assignment_type', 'user')->where('user_id', $user->id))
                    ->orWhere(fn ($q) => $q->where('assignment_type', 'group')->whereIn('group_id', $groupIds))
                    ->orWhere(fn ($q) => $q->where('assignment_type', 'department')->where('department_id', $user->department_id))
                    ->orWhere('assignment_type', 'default');
            })
            ->orderByRaw("CASE assignment_type WHEN 'user' THEN 1 WHEN 'group' THEN 2 WHEN 'department' THEN 3 ELSE 4 END")
            ->orderBy('priority')
            ->first();

        if (! $assignment) {
            return SignatureTemplate::where('is_default', true)->where('is_active', true)->first();
        }

        return SignatureTemplate::whereKey($assignment->template_id)->where('is_active', true)->first();
    }
}
