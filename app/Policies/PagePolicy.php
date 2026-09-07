<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class PagePolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user, string $organizationId): bool
    {
        return $this->allowed($user, $organizationId, 'pages.view');
    }

    public function view(User $user, Page $page): bool
    {
        return $this->allowed($user, (string) $page->organization_id, 'pages.view');
    }

    public function create(User $user, string $organizationId): bool
    {
        return $this->allowed($user, $organizationId, 'pages.create');
    }

    public function update(User $user, Page $page): bool
    {
        return $this->allowed($user, (string) $page->organization_id, 'pages.update');
    }

    public function delete(User $user, Page $page): bool
    {
        return $this->allowed($user, (string) $page->organization_id, 'pages.delete');
    }

    public function publish(User $user, Page $page): bool
    {
        return $this->allowed($user, (string) $page->organization_id, 'pages.publish');
    }
}
