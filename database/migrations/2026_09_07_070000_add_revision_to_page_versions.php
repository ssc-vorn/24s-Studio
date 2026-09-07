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

        // The Supabase foundation migration already owns this index in some
        // environments. Add it only when the index is not already present.
        $indexExists = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes('page_versions');

        if (! array_key_exists('page_versions_page_id_revision_index', $indexExists)) {
            Schema::table('page_versions', function (Blueprint $table) {
                $table->index(['page_id', 'revision']);
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
