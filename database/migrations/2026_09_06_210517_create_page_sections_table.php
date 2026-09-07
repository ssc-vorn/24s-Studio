<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_sections')) {
            return;
        }

        Schema::create('page_sections', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('page_version_id');
            $table->uuid('parent_id')->nullable();
            $table->string('type');
            $table->string('variant')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->jsonb('content')->nullable();
            $table->jsonb('styles')->nullable();
            $table->jsonb('responsive')->nullable();
            $table->jsonb('animation')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestampsTz();

            $table->primary('id');
            $table->unique('id', 'page_sections_id_unique');
            $table->index(['page_version_id', 'parent_id', 'position']);
            $table->index(['page_version_id', 'type']);
        });

        Schema::table('page_sections', function (Blueprint $table) {
            $table->foreign('page_version_id', 'page_sections_page_version_id_foreign')
                ->references('id')
                ->on('page_versions')
                ->cascadeOnDelete();

            $table->foreign('parent_id', 'page_sections_parent_id_foreign')
                ->references('id')
                ->on('page_sections')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
    }
};
