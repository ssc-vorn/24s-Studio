<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class ServicePolicy
{
    use ChecksOrganizationAccess;
    public function viewAny(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'services.view'); }
    public function view(User $user, Service $service): bool { return $this->allowed($user, $service->organization_id, 'services.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'services.create'); }
    public function update(User $user, Service $service): bool { return $this->allowed($user, $service->organization_id, 'services.update'); }
    public function delete(User $user, Service $service): bool { return $this->allowed($user, $service->organization_id, 'services.delete'); }
}
