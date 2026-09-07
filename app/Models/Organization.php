<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name', 'slug', 'status', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    /** @return BelongsToMany<User> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['is_owner', 'role'])->withTimestamps();
    }

    /** @return HasMany<Page> */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }
}
