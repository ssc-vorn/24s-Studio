<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Page;
use App\Models\PageVersion;
use App\Support\Tenancy\OrganizationContext;
use Inertia\Inertia;
use Inertia\Response;

class BuilderController extends Controller
{
    public function show(Organization $organization, Page $page, PageVersion $version): Response
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        abort_unless((string) $version->page_id === (string) $page->getKey(), 404);

        app(OrganizationContext::class)->assertMember((string) $organization->getKey());
        $this->authorize('view', $page);

        $version->load('sections');

        return Inertia::render('Admin/CMS/Builder/Show', [
            'organization' => [
                'id' => (string) $organization->getKey(),
                'name' => $organization->name,
            ],
            'page' => [
                'id' => (string) $page->getKey(),
                'title' => $page->title,
                'slug' => $page->slug,
            ],
            'version' => [
                'id' => (string) $version->getKey(),
                'version' => (int) $version->version,
                'revision' => (int) $version->revision,
                'status' => $version->status,
                'sections' => $version->sections,
            ],
        ]);
    }
}
