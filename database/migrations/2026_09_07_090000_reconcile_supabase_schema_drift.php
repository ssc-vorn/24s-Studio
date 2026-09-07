<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Supabase is the canonical production schema. This migration repairs
        // drift left behind by guarded create-table migrations without dropping
        // existing tables or data.
        if (Schema::hasTable('media')) {
            DB::table('media')->whereNull('mime_type')->update(['mime_type' => 'application/octet-stream']);
            DB::table('media')->whereNull('size')->update(['size' => 0]);
            DB::statement("alter table media alter column mime_type set not null");
            DB::statement("alter table media alter column size set default 0");
            DB::statement("alter table media alter column size set not null");
            DB::statement("alter table media alter column metadata set default '{}'::jsonb");
            DB::table('media')->whereNull('metadata')->update(['metadata' => DB::raw("'{}'::jsonb")]);
            DB::statement("alter table media alter column metadata set not null");
        }

        // The Supabase foundation uses before_data/after_data for audit payloads.
        // Rename the Laravel-only legacy names when they exist; leave the
        // canonical columns untouched on already-reconciled databases.
        if (Schema::hasTable('audit_logs')) {
            if (Schema::hasColumn('audit_logs', 'before') && ! Schema::hasColumn('audit_logs', 'before_data')) {
                Schema::table('audit_logs', function (Blueprint $table) {
                    $table->renameColumn('before', 'before_data');
                });
            }

            if (Schema::hasColumn('audit_logs', 'after') && ! Schema::hasColumn('audit_logs', 'after_data')) {
                Schema::table('audit_logs', function (Blueprint $table) {
                    $table->renameColumn('after', 'after_data');
                });
            }
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: this migration reconciles an existing
        // canonical Supabase schema and must not restore known schema drift.
    }
};
