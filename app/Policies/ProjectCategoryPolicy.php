<?php

namespace App\Policies;

use App\Models\ProjectCategory;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class ProjectCategoryPolicy
{
    use ChecksOrganizationAccess;

    public function view(User $user, ProjectCategory $category): bool { return $this->allowed($user, $category->organization_id, 'projects.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'projects.create'); }
    public function update(User $user, ProjectCategory $category): bool { return $this->allowed($user, $category->organization_id, 'projects.update'); }
    public function delete(User $user, ProjectCategory $category): bool { return $this->allowed($user, $category->organization_id, 'projects.delete'); }
}
