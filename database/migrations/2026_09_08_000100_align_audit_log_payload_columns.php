<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('audit_logs', 'before') && ! Schema::hasColumn('audit_logs', 'before_data')) {
                $table->renameColumn('before', 'before_data');
            }

            if (Schema::hasColumn('audit_logs', 'after') && ! Schema::hasColumn('audit_logs', 'after_data')) {
                $table->renameColumn('after', 'after_data');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('audit_logs', 'before_data') && ! Schema::hasColumn('audit_logs', 'before')) {
                $table->renameColumn('before_data', 'before');
            }

            if (Schema::hasColumn('audit_logs', 'after_data') && ! Schema::hasColumn('audit_logs', 'after')) {
                $table->renameColumn('after_data', 'after');
            }
        });
    }
};
