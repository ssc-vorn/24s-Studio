<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['organization_id','client_name','company','quote','avatar','rating','is_active','sort_order'];
    protected function casts(): array { return ['rating'=>'integer','is_active'=>'boolean','sort_order'=>'integer']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
}
