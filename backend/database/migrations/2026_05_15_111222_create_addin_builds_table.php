<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addin_builds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('config_id')->constrained('addin_configs')->cascadeOnDelete();
            $table->string('version');
            $table->string('status')->default('created');
            $table->string('manifest_path')->nullable();
            $table->string('package_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addin_builds');
    }
};
