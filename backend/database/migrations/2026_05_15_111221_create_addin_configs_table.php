<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addin_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('default');
            $table->string('api_base_url');
            $table->string('manifest_id')->nullable();
            $table->string('manifest_version')->default('1.0.0.0');
            $table->string('provider_name')->default('TRINOX');
            $table->string('display_name')->default('TRINOX Signature Manager');
            $table->string('support_url')->nullable();
            $table->string('taskpane_url')->default('https://localhost:5173/index.html');
            $table->string('autorun_url')->default('https://localhost:5173/autorun.html');
            $table->string('icon_url')->nullable();
            $table->string('highres_icon_url')->nullable();
            $table->text('allowed_domains')->nullable();
            $table->string('tenant_mode')->default('manual');
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addin_configs');
    }
};
