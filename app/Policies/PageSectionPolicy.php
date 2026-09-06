<?php

namespace App\Policies;

use App\Models\PageSection;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class PageSectionPolicy
{
    use ChecksOrganizationAccess;

    private function organizationId(PageSection $section): string
    {
        return (string) $section->pageVersion->page->organization_id;
    }

    public function view(User $user, PageSection $section): bool { return $this->allowed($user, $this->organizationId($section), 'pages.view'); }
    public function update(User $user, PageSection $section): bool { return $this->allowed($user, $this->organizationId($section), 'pages.update'); }
    public function delete(User $user, PageSection $section): bool { return $this->allowed($user, $this->organizationId($section), 'pages.update'); }
    public function reorder(User $user, PageSection $section): bool { return $this->allowed($user, $this->organizationId($section), 'pages.update'); }
}
