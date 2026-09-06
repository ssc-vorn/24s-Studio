<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectCategory extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['organization_id','name','slug'];
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function projects(): HasMany { return $this->hasMany(Project::class, 'category_id'); }
}
