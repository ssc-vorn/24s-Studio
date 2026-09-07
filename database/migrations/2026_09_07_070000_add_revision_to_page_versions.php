<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_versions', function (Blueprint $table) {
            $table->unsignedBigInteger('revision')->default(1)->after('status');
            $table->index(['page_id', 'revision']);
        });
    }

    public function down(): void
    {
        Schema::table('page_versions', function (Blueprint $table) {
            $table->dropIndex(['page_id', 'revision']);
            $table->dropColumn('revision');
        });
    }
};
