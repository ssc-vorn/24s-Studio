<?php

namespace App\Policies;

use App\Models\Theme;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class ThemePolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'themes.view'); }
    public function view(User $user, Theme $theme): bool { return $this->allowed($user, $theme->organization_id, 'themes.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'themes.create'); }
    public function update(User $user, Theme $theme): bool { return $this->allowed($user, $theme->organization_id, 'themes.update'); }
    public function delete(User $user, Theme $theme): bool { return $this->allowed($user, $theme->organization_id, 'themes.delete'); }
}
