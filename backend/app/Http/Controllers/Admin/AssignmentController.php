<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Group;
use App\Models\SignatureAssignment;
use App\Models\SignatureTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = SignatureAssignment::with(['user', 'group', 'department', 'template'])->latest()->paginate(20);
        $assignments->getCollection()->transform(function (SignatureAssignment $assignment) {
            $targetLabel = match ($assignment->assignment_type) {
                'user' => $assignment->user?->email ?? $assignment->user?->name ?? '-',
                'group' => $assignment->group?->name ?? '-',
                'department' => $assignment->department?->name ?? '-',
                default => 'Tum aktif kullanicilar',
            };

            $affectedUsers = match ($assignment->assignment_type) {
                'user' => $assignment->user_id ? 1 : 0,
                'group' => $assignment->group ? $assignment->group->users()->where('is_active', true)->count() : 0,
                'department' => $assignment->department ? $assignment->department->users()->where('is_active', true)->count() : 0,
                default => User::query()->where('is_active', true)->count(),
            };

            $assignment->target_label = $targetLabel;
            $assignment->affected_users_count = $affectedUsers;

            return $assignment;
        });

        return view('admin.assignments.index', [
            'assignments' => $assignments,
            'users' => User::orderBy('name')->get(),
            'groups' => Group::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'templates' => SignatureTemplate::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedPayload($request, true);

        SignatureAssignment::create($data);

        return redirect()->route('admin.assignments.index')->with('success', 'Atama olusturuldu.');
    }

    public function destroy(SignatureAssignment $assignment)
    {
        $assignment->delete();

        return redirect()->route('admin.assignments.index')->with('success', 'Atama silindi.');
    }

    public function update(Request $request, SignatureAssignment $assignment)
    {
        $data = $this->validatedPayload($request, true);

        $assignment->update($data);

        return redirect()->route('admin.assignments.index')->with('success', 'Atama guncellendi.');
    }

    private function validatedPayload(Request $request, bool $defaultActive = true): array
    {
        $data = $request->validate([
            'assignment_type' => ['required', 'in:user,group,department,default'],
            'user_id' => ['nullable', 'exists:users,id'],
            'group_id' => ['nullable', 'exists:groups,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'template_id' => ['required', 'exists:signature_templates,id'],
            'priority' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $this->validateScopeTarget($data);

        $data['is_active'] = (bool) ($data['is_active'] ?? $defaultActive);
        $data['priority'] = $data['priority'] ?? 100;
        $data['starts_at'] = ! empty($data['starts_at']) ? Carbon::parse($data['starts_at'])->startOfMinute() : null;
        $data['ends_at'] = ! empty($data['ends_at']) ? Carbon::parse($data['ends_at'])->startOfMinute() : null;

        return $data;
    }

    private function validateScopeTarget(array &$data): void
    {
        $data['user_id'] = $data['assignment_type'] === 'user' ? ($data['user_id'] ?? null) : null;
        $data['group_id'] = $data['assignment_type'] === 'group' ? ($data['group_id'] ?? null) : null;
        $data['department_id'] = $data['assignment_type'] === 'department' ? ($data['department_id'] ?? null) : null;

        $targetId = match ($data['assignment_type']) {
            'user' => $data['user_id'],
            'group' => $data['group_id'],
            'department' => $data['department_id'],
            default => 1,
        };

        if (! $targetId) {
            throw ValidationException::withMessages([
                'assignment_type' => 'Secilen kapsama uygun hedef secilmelidir.',
            ]);
        }
    }
}
