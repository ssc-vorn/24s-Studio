<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('title'); $table->string('slug');
            $table->text('excerpt')->nullable(); $table->longText('description')->nullable();
            $table->string('icon')->nullable(); $table->jsonb('metadata')->nullable();
            $table->boolean('is_active')->default(true); $table->unsignedInteger('sort_order')->default(0);
            $table->timestampsTz();
            $table->unique(['organization_id','slug']);
            $table->index(['organization_id','is_active','sort_order']);
        });

        Schema::create('project_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->string('slug'); $table->timestampsTz();
            $table->unique(['organization_id','slug']);
        });

        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('project_categories')->nullOnDelete();
            $table->string('title'); $table->string('slug');
            $table->text('excerpt')->nullable(); $table->longText('description')->nullable();
            $table->string('client_name')->nullable();
            $table->foreignUuid('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->jsonb('content')->nullable();
            $table->boolean('is_featured')->default(false); $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0); $table->timestampsTz();
            $table->unique(['organization_id','slug']);
            $table->index(['organization_id','is_published','is_featured']);
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title'); $table->string('slug'); $table->text('excerpt')->nullable();
            $table->longText('content')->nullable(); $table->string('cover_image')->nullable();
            $table->string('status')->default('draft'); $table->timestampTz('published_at')->nullable();
            $table->jsonb('seo')->nullable(); $table->timestampsTz();
            $table->unique(['organization_id','slug']); $table->index(['organization_id','status','published_at']);
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('client_name'); $table->string('company')->nullable(); $table->text('quote');
            $table->string('avatar')->nullable(); $table->unsignedTinyInteger('rating')->nullable();
            $table->boolean('is_active')->default(true); $table->unsignedInteger('sort_order')->default(0); $table->timestampsTz();
            $table->index(['organization_id','is_active','sort_order']);
        });

        Schema::create('partners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->string('logo')->nullable(); $table->string('website')->nullable();
            $table->boolean('is_active')->default(true); $table->unsignedInteger('sort_order')->default(0); $table->timestampsTz();
            $table->index(['organization_id','is_active','sort_order']);
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name'); $table->string('email'); $table->string('phone')->nullable();
            $table->string('company')->nullable(); $table->string('service')->nullable(); $table->text('message')->nullable();
            $table->string('status')->default('new'); $table->jsonb('metadata')->nullable();
            $table->timestampTz('contacted_at')->nullable(); $table->timestampsTz();
            $table->index(['organization_id','status','created_at']); $table->index(['organization_id','email']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); $table->string('auditable_type')->nullable(); $table->string('auditable_id')->nullable();
            $table->jsonb('before')->nullable(); $table->jsonb('after')->nullable();
            $table->ipAddress('ip_address')->nullable(); $table->text('user_agent')->nullable(); $table->timestampsTz();
            $table->index(['organization_id','created_at']); $table->index(['auditable_type','auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs'); Schema::dropIfExists('leads'); Schema::dropIfExists('partners');
        Schema::dropIfExists('testimonials'); Schema::dropIfExists('blog_posts'); Schema::dropIfExists('projects');
        Schema::dropIfExists('project_categories'); Schema::dropIfExists('services');
    }
};
