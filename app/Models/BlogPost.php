<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = ['organization_id','author_id','title','slug','excerpt','content','cover_image','status','published_at','seo'];
    protected function casts(): array { return ['published_at'=>'datetime','seo'=>'array']; }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
}
