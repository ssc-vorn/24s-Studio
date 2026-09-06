<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageVersion extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['page_id', 'version', 'status', 'content', 'created_by', 'published_at'];

    protected function casts(): array { return ['content' => 'array', 'published_at' => 'datetime']; }

    public function page(): BelongsTo { return $this->belongsTo(Page::class); }
    public function sections(): HasMany { return $this->hasMany(PageSection::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
