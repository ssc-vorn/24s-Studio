<?php

namespace App\Http\Controllers;

use App\Domain\CMS\Actions\CreatePage;
use App\Domain\CMS\Actions\CreatePageVersion;
use App\Domain\CMS\DTOs\PageData;
use App\Http\Requests\CMS\StorePageRequest;
use App\Models\Organization;
use App\Models\Page;
use App\Support\Tenancy\OrganizationContext;
use App\Support\Tenancy\OrganizationAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $organizations = $user->organizations()->orderBy('name')->get(['organizations.id', 'organizations.name']);
        $organizationId = (string) $request->query('organization', $organizations->first()?->getKey() ?? '');
        $organization = $organizations->firstWhere('id', $organizationId);

        abort_if($organizationId !== '' && !$organization, 404);

        $access = app(OrganizationAccess::class);
        $canViewPages = $organization && $access->can($user, (string) $organization->getKey(), 'pages.view');
        $pages = $canViewPages
            ? Page::query()
                ->where('organization_id', $organization->getKey())
                ->with('latestVersion:id,page_id,version,status')
                ->latest('updated_at')
                ->get()
                ->map(fn (Page $page) => [
                    'id' => (string) $page->getKey(),
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'status' => $page->status,
                    'updated_at' => $page->updated_at?->toISOString(),
                    'latest_version' => $page->latestVersion ? [
                        'id' => (string) $page->latestVersion->getKey(),
                        'number' => (int) $page->latestVersion->version,
                        'status' => $page->latestVersion->status,
                    ] : null,
                ])->values()
            : collect();

        return Inertia::render('Dashboard', [
            'organizations' => $organizations->map(fn (Organization $item) => [
                'id' => (string) $item->getKey(),
                'name' => $item->name,
            ])->values(),
            'selectedOrganizationId' => $organization ? (string) $organization->getKey() : null,
            'pages' => $pages,
            'permissions' => [
                'viewPages' => (bool) $canViewPages,
                'createPages' => (bool) ($organization && $access->can($user, (string) $organization->getKey(), 'pages.create')),
                'manageMembers' => (bool) ($organization && $access->can($user, (string) $organization->getKey(), 'members.manage')),
            ],
        ]);
    }

    public function store(
        StorePageRequest $request,
        Organization $organization,
        CreatePage $createPage,
        CreatePageVersion $createVersion,
    ): RedirectResponse {
        app(OrganizationContext::class)->assertMember((string) $organization->getKey());
        $this->authorize('create', [Page::class, (string) $organization->getKey()]);

        $page = $createPage->handle(
            PageData::fromArray($request->validated(), (string) $organization->getKey()),
            (int) $request->user()->getKey(),
        );
        $version = $createVersion->handle($page, [], (int) $request->user()->getKey());

        return redirect()->route('admin.cms.builder.show', [
            'organization' => $organization,
            'page' => $page,
            'version' => $version,
        ]);
    }
}
