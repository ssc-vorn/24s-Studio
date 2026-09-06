<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class MenuItemPolicy
{
    use ChecksOrganizationAccess;

    private function organizationId(MenuItem $item): string { return (string) $item->menu->organization_id; }
    public function view(User $user, MenuItem $item): bool { return $this->allowed($user, $this->organizationId($item), 'menu_items.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'menu_items.create'); }
    public function update(User $user, MenuItem $item): bool { return $this->allowed($user, $this->organizationId($item), 'menu_items.update'); }
    public function delete(User $user, MenuItem $item): bool { return $this->allowed($user, $this->organizationId($item), 'menu_items.delete'); }
    public function reorder(User $user, MenuItem $item): bool { return $this->allowed($user, $this->organizationId($item), 'menu_items.update'); }
}
