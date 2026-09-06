<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class BlogPostPolicy
{
    use ChecksOrganizationAccess;
    public function viewAny(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'blog.view'); }
    public function view(User $user, BlogPost $post): bool { return $this->allowed($user, $post->organization_id, 'blog.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'blog.create'); }
    public function update(User $user, BlogPost $post): bool { return $this->allowed($user, $post->organization_id, 'blog.update'); }
    public function delete(User $user, BlogPost $post): bool { return $this->allowed($user, $post->organization_id, 'blog.delete'); }
    public function publish(User $user, BlogPost $post): bool { return $this->allowed($user, $post->organization_id, 'blog.publish'); }
}
