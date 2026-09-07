<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Support\Tenancy\OrganizationAccess;

trait ChecksOrganizationAccess
{
    protected function member(User $user, string $organizationId): bool
    {
        return $user->organizations()->whereKey($organizationId)->exists();
    }

    protected function permission(User $user, string $permission): bool
    {
        return $user->can($permission);
    }

    protected function allowed(User $user, string $organizationId, string $permission): bool
    {
        return app(OrganizationAccess::class)->can($user, $organizationId, $permission);
    }
}
