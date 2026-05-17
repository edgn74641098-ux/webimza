<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('force_updates', function (Blueprint $table) {
            $table->string('scope_key')->nullable()->after('scope_type');
            $table->foreignId('group_id')->nullable()->after('department_id')->constrained('groups')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->after('group_id')->constrained('signature_templates')->nullOnDelete();
            $table->text('reason')->nullable()->after('status');
            $table->unsignedInteger('ttl_days')->nullable()->after('reason');
            $table->timestamp('expires_at')->nullable()->after('ttl_days');
            $table->unsignedInteger('affected_users')->nullable()->after('expires_at');
            $table->unsignedInteger('affected_devices')->nullable()->after('affected_users');
        });
    }

    public function down(): void
    {
        Schema::table('force_updates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
            $table->dropConstrainedForeignId('template_id');
            $table->dropColumn(['scope_key','reason','ttl_days','expires_at','affected_users','affected_devices']);
        });
    }
};
