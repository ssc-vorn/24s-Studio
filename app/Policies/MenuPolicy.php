<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class MenuPolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'menus.view'); }
    public function view(User $user, Menu $menu): bool { return $this->allowed($user, $menu->organization_id, 'menus.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'menus.create'); }
    public function update(User $user, Menu $menu): bool { return $this->allowed($user, $menu->organization_id, 'menus.update'); }
    public function delete(User $user, Menu $menu): bool { return $this->allowed($user, $menu->organization_id, 'menus.delete'); }
}
