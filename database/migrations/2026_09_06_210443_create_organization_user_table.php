<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_user', function (Blueprint $table) {
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_owner')->default(false);
            $table->timestampsTz();
            $table->primary(['organization_id', 'user_id']);
            $table->index('user_id');
            $table->index(['organization_id', 'is_owner']);
        });
    }

    public function down(): void { Schema::dropIfExists('organization_user'); }
};
