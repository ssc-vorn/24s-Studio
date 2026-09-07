<?php

namespace App\Support\Tenancy;

use App\Models\User;

final class OrganizationAccess
{
    /** @var array<string, list<string>> */
    private const ROLE_PERMISSIONS = [
        'owner' => ['*'],
        'admin' => ['*'],
        'editor' => ['pages.view', 'pages.create', 'pages.update', 'media.view', 'media.create', 'media.update', 'media.delete'],
        'reviewer' => ['pages.view', 'pages.approve'],
        'publisher' => ['pages.view', 'pages.publish'],
        'viewer' => ['pages.view', 'media.view'],
    ];

    /** @return list<string> */
    public static function roles(): array
    {
        return array_keys(self::ROLE_PERMISSIONS);
    }

    public function role(User $user, string $organizationId): ?string
    {
        $organization = $user->organizations()->whereKey($organizationId)->first();

        if (!$organization) {
            return null;
        }

        if ((bool) $organization->pivot?->is_owner) {
            return 'owner';
        }

        return $organization->pivot?->role;
    }

    public function can(User $user, string $organizationId, string $permission): bool
    {
        $role = $this->role($user, $organizationId);

        if (!$role) {
            return false;
        }

        $permissions = self::ROLE_PERMISSIONS[$role] ?? [];

        if (in_array('*', $permissions, true) || in_array($permission, $permissions, true)) {
            return true;
        }

        // Existing memberships without a scoped role remain compatible while
        // administrators migrate them to one of the roles above.
        return $user->organizations()->whereKey($organizationId)->exists()
            && ($user->can($permission) || ($permission === 'pages.approve' && $user->can('pages.publish')));
    }
}
