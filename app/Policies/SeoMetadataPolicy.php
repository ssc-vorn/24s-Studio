<?php

namespace App\Policies;

use App\Models\SeoMetadata;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class SeoMetadataPolicy
{
    use ChecksOrganizationAccess;

    public function view(User $user, SeoMetadata $seo): bool { return $this->allowed($user, $seo->organization_id, 'seo.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'seo.create'); }
    public function update(User $user, SeoMetadata $seo): bool { return $this->allowed($user, $seo->organization_id, 'seo.update'); }
    public function delete(User $user, SeoMetadata $seo): bool { return $this->allowed($user, $seo->organization_id, 'seo.delete'); }
}
