<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['organization_id','title','slug','excerpt','description','icon','metadata','is_active','sort_order'];
    protected function casts(): array { return ['metadata'=>'array','is_active'=>'boolean','sort_order'=>'integer']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
}
