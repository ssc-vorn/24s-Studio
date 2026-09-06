<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['organization_id', 'title', 'slug', 'status', 'template', 'is_homepage', 'metadata', 'created_by', 'updated_by'];

    protected function casts(): array { return ['is_homepage' => 'boolean', 'metadata' => 'array']; }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function versions(): HasMany { return $this->hasMany(PageVersion::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function updater(): BelongsTo { return $this->belongsTo(User::class, 'updated_by'); }
}
