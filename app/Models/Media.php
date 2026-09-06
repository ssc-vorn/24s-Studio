<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Media extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'organization_id', 'path', 'filename', 'mime_type', 'size',
        'width', 'height', 'alt', 'metadata', 'created_by',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array', 'size' => 'integer', 'width' => 'integer', 'height' => 'integer'];
    }

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
