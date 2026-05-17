<?php

namespace Tests\Unit;

use App\Models\Department;
use App\Models\Group;
use App\Models\SignatureAssignment;
use App\Models\SignatureTemplate;
use App\Models\User;
use App\Services\SignatureAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignatureAssignmentPriorityTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_assignment_with_expected_priority_order(): void
    {
        $department = Department::create([
            'name' => 'IT',
            'is_active' => true,
        ]);

        $group = Group::create([
            'name' => 'Ops',
            'code' => 'ops',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'department_id' => $department->id,
        ]);
        $user->groups()->attach($group->id);

        $defaultTemplate = SignatureTemplate::create([
            'name' => 'Default',
            'type' => 'both',
            'html_content' => '<p>default</p>',
            'text_content' => 'default',
            'version' => '1.0.0',
            'is_active' => true,
        ]);

        $departmentTemplate = SignatureTemplate::create([
            'name' => 'Department',
            'type' => 'both',
            'html_content' => '<p>department</p>',
            'text_content' => 'department',
            'version' => '1.0.0',
            'is_active' => true,
        ]);

        $groupTemplate = SignatureTemplate::create([
            'name' => 'Group',
            'type' => 'both',
            'html_content' => '<p>group</p>',
            'text_content' => 'group',
            'version' => '1.0.0',
            'is_active' => true,
        ]);

        $userTemplate = SignatureTemplate::create([
            'name' => 'User',
            'type' => 'both',
            'html_content' => '<p>user</p>',
            'text_content' => 'user',
            'version' => '1.0.0',
            'is_active' => true,
        ]);

        SignatureAssignment::create([
            'assignment_type' => 'default',
            'template_id' => $defaultTemplate->id,
            'priority' => 1,
            'is_active' => true,
        ]);
        SignatureAssignment::create([
            'assignment_type' => 'department',
            'department_id' => $department->id,
            'template_id' => $departmentTemplate->id,
            'priority' => 1,
            'is_active' => true,
        ]);
        SignatureAssignment::create([
            'assignment_type' => 'group',
            'group_id' => $group->id,
            'template_id' => $groupTemplate->id,
            'priority' => 1,
            'is_active' => true,
        ]);
        SignatureAssignment::create([
            'assignment_type' => 'user',
            'user_id' => $user->id,
            'template_id' => $userTemplate->id,
            'priority' => 1,
            'is_active' => true,
        ]);

        $service = app(SignatureAssignmentService::class);
        $resolved = $service->resolveForUser($user);

        $this->assertNotNull($resolved);
        $this->assertSame($userTemplate->id, $resolved->id);
    }
}
