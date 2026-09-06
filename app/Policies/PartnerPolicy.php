<?php

namespace App\Policies;

use App\Models\Partner;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class PartnerPolicy
{
    use ChecksOrganizationAccess;

    public function view(User $user, Partner $partner): bool { return $this->allowed($user, $partner->organization_id, 'partners.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'partners.create'); }
    public function update(User $user, Partner $partner): bool { return $this->allowed($user, $partner->organization_id, 'partners.update'); }
    public function delete(User $user, Partner $partner): bool { return $this->allowed($user, $partner->organization_id, 'partners.delete'); }
}
