<?php

namespace App\Policies;

use App\Models\PageVersion;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class PageVersionPolicy
{
    use ChecksOrganizationAccess;

    private function organizationId(PageVersion $version): string
    {
        return (string) $version->page->organization_id;
    }

    public function view(User $user, PageVersion $version): bool { return $this->allowed($user, $this->organizationId($version), 'pages.view'); }
    public function update(User $user, PageVersion $version): bool { return $this->allowed($user, $this->organizationId($version), 'pages.update'); }
    public function restore(User $user, PageVersion $version): bool { return $this->allowed($user, $this->organizationId($version), 'pages.update'); }
    public function publish(User $user, PageVersion $version): bool { return $this->allowed($user, $this->organizationId($version), 'pages.publish'); }
}
