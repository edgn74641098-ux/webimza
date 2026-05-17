<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addin_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->index();
            $table->string('display_name')->nullable();
            $table->string('client_type')->default('unknown');
            $table->string('platform')->nullable();
            $table->string('host')->nullable();
            $table->string('office_version')->nullable();
            $table->string('addin_version')->nullable();
            $table->string('device_id')->unique();
            $table->string('last_ip', 45)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->string('last_signature_version')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addin_devices');
    }
};
