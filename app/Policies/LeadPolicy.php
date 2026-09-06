<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class LeadPolicy
{
    use ChecksOrganizationAccess;

    public function view(User $user, Lead $lead): bool { return $this->allowed($user, $lead->organization_id, 'leads.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'leads.create'); }
    public function update(User $user, Lead $lead): bool { return $this->allowed($user, $lead->organization_id, 'leads.update'); }
    public function delete(User $user, Lead $lead): bool { return $this->allowed($user, $lead->organization_id, 'leads.delete'); }
}
