<?php

namespace App\Support\Tenancy;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class OrganizationContext
{
    public function user(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);
        return $user;
    }

    public function assertMember(string $organizationId): Organization
    {
        $organization = Organization::query()->findOrFail($organizationId);

        if (!$this->user()->organizations()->whereKey($organization->getKey())->exists()) {
            throw new AccessDeniedHttpException('You are not a member of this organization.');
        }

        return $organization;
    }

    public function can(string $organizationId, string $permission): bool
    {
        return app(OrganizationAccess::class)->can($this->user(), $organizationId, $permission);
    }
}
