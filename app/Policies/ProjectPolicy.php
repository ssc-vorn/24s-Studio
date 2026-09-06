<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class ProjectPolicy
{
    use ChecksOrganizationAccess;
    public function viewAny(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'projects.view'); }
    public function view(User $user, Project $project): bool { return $this->allowed($user, $project->organization_id, 'projects.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'projects.create'); }
    public function update(User $user, Project $project): bool { return $this->allowed($user, $project->organization_id, 'projects.update'); }
    public function delete(User $user, Project $project): bool { return $this->allowed($user, $project->organization_id, 'projects.delete'); }
    public function publish(User $user, Project $project): bool { return $this->allowed($user, $project->organization_id, 'projects.publish'); }
}
