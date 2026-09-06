<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('page_version_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('page_sections')->cascadeOnDelete();
            $table->string('type');
            $table->string('variant')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->jsonb('content')->nullable();
            $table->jsonb('styles')->nullable();
            $table->jsonb('responsive')->nullable();
            $table->jsonb('animation')->nullable();
            $table->boolean('visibility')->default(true);
            $table->timestampsTz();
            $table->index(['page_version_id', 'parent_id', 'position']);
            $table->index(['page_version_id', 'type']);
        });
    }

    public function down(): void { Schema::dropIfExists('page_sections'); }
};
