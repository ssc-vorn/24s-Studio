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
        $this->assertAccess($organization, $page, $version);
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

    public function preview(Organization $organization, Page $page, PageVersion $version): Response
    {
        $this->assertAccess($organization, $page, $version);
        $version->load(['sections' => fn ($query) => $query->where('is_visible', true)->orderBy('position')]);

        return Inertia::render('Public/CmsPage', [
            'page' => [
                'id' => (string) $page->getKey(),
                'title' => $page->title,
                'slug' => $page->slug,
                'template' => $page->template,
                'metadata' => $page->metadata ?? [],
            ],
            'version' => [
                'id' => (string) $version->getKey(),
                'version' => (int) $version->version,
                'published_at' => $version->published_at?->toISOString(),
            ],
            'sections' => $version->sections->values(),
            'preview' => true,
        ]);
    }

    private function assertAccess(Organization $organization, Page $page, PageVersion $version): void
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        abort_unless((string) $version->page_id === (string) $page->getKey(), 404);
        app(OrganizationContext::class)->assertMember((string) $organization->getKey());
        $this->authorize('view', $page);
    }
}
