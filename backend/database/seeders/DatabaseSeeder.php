<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\SignatureAssignment;
use App\Models\SignatureTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::firstOrCreate(['name' => 'Bilgi Islem'], ['description' => 'IT Department']);

        $user = User::updateOrCreate(
            ['email' => 'erkan.degnekci@trinoxmetal.com'],
            [
                'name' => 'Erkan Degnekci',
                'department_id' => $department->id,
                'title' => 'Bilgi Teknolojileri Uzmani',
                'company' => 'TRINOX Metal',
                'phone' => '+90 212 000 00 00',
                'mobile' => '+90 555 000 00 00',
                'website' => 'https://trinoxmetal.com',
                'source' => 'manual',
                'password' => bcrypt('password'),
            ]
        );

        $template = SignatureTemplate::updateOrCreate(
            ['name' => 'TRINOX BT Signature'],
            [
                'description' => 'Default IT signature template',
                'type' => 'both',
                'html_content' => '<table style="font-family:Arial;font-size:12px;color:#333;"><tr><td><strong>{{DisplayName}}</strong><br>{{Title}}<br>{{Department}}<br>{{Company}}<br>Tel: {{Phone}}<br>{{#if Mobile}}Mobil: {{Mobile}}<br>{{/if}}E-posta: {{Email}}<br>Web: {{Website}}</td></tr></table>',
                'text_content' => "{{DisplayName}}\n{{Title}}\n{{Department}}\n{{Company}}\nTel: {{Phone}}\n{{#if Mobile}}Mobil: {{Mobile}}\n{{/if}}E-posta: {{Email}}\nWeb: {{Website}}",
                'is_default' => true,
                'is_active' => true,
                'version' => '2026.05.15.001',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]
        );

        SignatureAssignment::updateOrCreate(
            ['assignment_type' => 'department', 'department_id' => $department->id],
            ['template_id' => $template->id, 'priority' => 1, 'is_active' => true]
        );
    }
}
