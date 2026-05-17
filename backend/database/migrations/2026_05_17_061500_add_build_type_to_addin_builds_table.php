<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addin_builds', function (Blueprint $table) {
            if (! Schema::hasColumn('addin_builds', 'build_type')) {
                $table->string('build_type')->default('manual_basic')->after('config_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('addin_builds', function (Blueprint $table) {
            if (Schema::hasColumn('addin_builds', 'build_type')) {
                $table->dropColumn('build_type');
            }
        });
    }
};
