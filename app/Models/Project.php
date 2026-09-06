<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['organization_id','category_id','title','slug','excerpt','description','client_name','cover_media_id','content','is_featured','is_published','sort_order'];
    protected function casts(): array { return ['content'=>'array','is_featured'=>'boolean','is_published'=>'boolean','sort_order'=>'integer']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function category(): BelongsTo { return $this->belongsTo(ProjectCategory::class, 'category_id'); }
    public function coverMedia(): BelongsTo { return $this->belongsTo(Media::class, 'cover_media_id'); }
}
