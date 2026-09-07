<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('organization_user', 'role')) {
            Schema::table('organization_user', function (Blueprint $table) {
                $table->string('role')->nullable()->after('is_owner');
                $table->index(['organization_id', 'role']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('organization_user', 'role')) {
            Schema::table('organization_user', function (Blueprint $table) {
                $table->dropIndex(['organization_id', 'role']);
                $table->dropColumn('role');
            });
        }
    }
};
