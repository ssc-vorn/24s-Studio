<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('page_versions')) {
            return;
        }

        if (! Schema::hasColumn('page_versions', 'revision')) {
            Schema::table('page_versions', function (Blueprint $table) {
                $table->unsignedBigInteger('revision')->default(1)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('page_versions')) {
            return;
        }

        if (Schema::hasColumn('page_versions', 'revision')) {
            Schema::table('page_versions', function (Blueprint $table) {
                $table->dropColumn('revision');
            });
        }
    }
};
