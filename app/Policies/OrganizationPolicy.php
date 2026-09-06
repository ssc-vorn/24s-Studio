<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    public function view(User $user, Organization $organization): bool
    {
        return $user->organizations()->whereKey($organization->getKey())->exists()
            && $user->can('organizations.view');
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->organizations()->whereKey($organization->getKey())->exists()
            && $user->can('organizations.update');
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->organizations()->whereKey($organization->getKey())->exists()
            && $user->can('organizations.delete')
            && (bool) $organization->users()->whereKey($user->getKey())->first()?->pivot?->is_owner;
    }
}
