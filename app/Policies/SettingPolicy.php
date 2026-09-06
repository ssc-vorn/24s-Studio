<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class SettingPolicy
{
    use ChecksOrganizationAccess;

    public function view(User $user, Setting $setting): bool { return $this->allowed($user, $setting->organization_id, 'settings.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'settings.create'); }
    public function update(User $user, Setting $setting): bool { return $this->allowed($user, $setting->organization_id, 'settings.update'); }
    public function delete(User $user, Setting $setting): bool { return $this->allowed($user, $setting->organization_id, 'settings.delete'); }
}
