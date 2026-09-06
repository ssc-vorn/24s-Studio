<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Partner extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['organization_id','name','logo','website','is_active','sort_order'];
    protected function casts(): array { return ['is_active'=>'boolean','sort_order'=>'integer']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
}
