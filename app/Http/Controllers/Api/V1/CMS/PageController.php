<?php

namespace App\Http\Controllers\Api\V1\CMS;

use App\Domain\CMS\Actions\CreatePage;
use App\Domain\CMS\Actions\CreatePageVersion;
use App\Domain\CMS\Actions\DeletePage;
use App\Domain\CMS\Actions\PublishPage;
use App\Domain\CMS\Actions\RestorePageVersion;
use App\Domain\CMS\Actions\TransitionPageVersionStatus;
use App\Domain\CMS\Actions\UpdatePage;
use App\Domain\CMS\DTOs\PageData;
use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StorePageRequest;
use App\Http\Requests\CMS\StorePageVersionRequest;
use App\Http\Requests\CMS\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Http\Resources\PageVersionResource;
use App\Models\Organization;
use App\Models\Page;
use App\Models\PageVersion;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index(Request $request, Organization $organization): JsonResponse
    {
        app(OrganizationContext::class)->assertMember((string) $organization->getKey());
        $this->authorize('viewAny', [Page::class, (string) $organization->getKey()]);
        $pages = $organization->pages()->latest()->paginate(min((int) $request->integer('per_page', 20), 100));
        return PageResource::collection($pages)->response();
    }

    public function store(StorePageRequest $request, Organization $organization, CreatePage $action): JsonResponse
    {
        app(OrganizationContext::class)->assertMember((string) $organization->getKey());
        $this->authorize('create', [Page::class, (string) $organization->getKey()]);
        $page = $action->handle(PageData::fromArray($request->validated(), (string) $organization->getKey()), (int) $request->user()->getKey());
        return (new PageResource($page))->response()->setStatusCode(201);
    }

    public function show(Organization $organization, Page $page): PageResource
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        $this->authorize('view', $page);
        return new PageResource($page);
    }

    public function update(UpdatePageRequest $request, Organization $organization, Page $page, UpdatePage $action): PageResource
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        $this->authorize('update', $page);
        $current = $page->only(['title', 'slug', 'status', 'template', 'is_homepage', 'metadata']);
        $data = array_merge($current, $request->validated());
        return new PageResource($action->handle($page, PageData::fromArray($data, (string) $organization->getKey()), (int) $request->user()->getKey()));
    }

    public function destroy(Organization $organization, Page $page, DeletePage $action): JsonResponse
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        $this->authorize('delete', $page);
        $action->handle($page);
        return response()->json(null, 204);
    }

    public function versions(Organization $organization, Page $page): mixed
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        $this->authorize('view', $page);
        return PageVersionResource::collection($page->versions()->with('creator')->latest('version')->paginate(20));
    }

    public function version(Organization $organization, Page $page, PageVersion $version): PageVersionResource
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        abort_unless((string) $version->page_id === (string) $page->getKey(), 404);
        $this->authorize('view', $page);
        return new PageVersionResource($version->load(['creator', 'sections']));
    }

    public function createVersion(StorePageVersionRequest $request, Organization $organization, Page $page, CreatePageVersion $action): JsonResponse
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        $this->authorize('update', $page);
        $version = $action->handle($page, $request->validated()['content'], (int) $request->user()->getKey());
        return (new PageVersionResource($version))->response()->setStatusCode(201);
    }

    public function restoreVersion(Request $request, Organization $organization, Page $page, PageVersion $version, RestorePageVersion $action): PageVersionResource
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        abort_unless((string) $version->page_id === (string) $page->getKey(), 404);
        $this->authorize('update', $page);
        return new PageVersionResource($action->handle($page, $version, (int) $request->user()->getKey()));
    }

    public function submitReview(Organization $organization, Page $page, PageVersion $version, TransitionPageVersionStatus $action): PageVersionResource
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        abort_unless((string) $version->page_id === (string) $page->getKey(), 404);
        $this->authorize('update', $page);
        return new PageVersionResource($action->handle($page, $version, 'submit-review'));
    }

    public function approve(Organization $organization, Page $page, PageVersion $version, TransitionPageVersionStatus $action): PageVersionResource
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        abort_unless((string) $version->page_id === (string) $page->getKey(), 404);
        $this->authorize('publish', $page);
        return new PageVersionResource($action->handle($page, $version, 'approve'));
    }

    public function publish(Organization $organization, Page $page, PageVersion $version, PublishPage $action): PageResource
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        abort_unless((string) $version->page_id === (string) $page->getKey(), 404);
        $this->authorize('publish', $page);
        return new PageResource($action->handle($page, $version));
    }
}
