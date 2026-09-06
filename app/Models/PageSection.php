<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageSection extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['page_version_id', 'parent_id', 'type', 'variant', 'position', 'content', 'styles', 'responsive', 'animation', 'visibility'];

    protected function casts(): array { return ['content' => 'array', 'styles' => 'array', 'responsive' => 'array', 'animation' => 'array', 'visibility' => 'boolean']; }

    public function pageVersion(): BelongsTo { return $this->belongsTo(PageVersion::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id')->orderBy('position'); }
}
