<?php

namespace App\Http\Controllers\Api\V1\CMS;

use App\Domain\CMS\Actions\CreatePageSection;
use App\Domain\CMS\Actions\DeletePageSection;
use App\Domain\CMS\Actions\ReorderPageSections;
use App\Domain\CMS\Actions\UpdatePageSection;
use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StorePageSectionRequest;
use App\Http\Requests\CMS\UpdatePageSectionRequest;
use App\Http\Resources\PageSectionResource;
use App\Models\Organization;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageSectionController extends Controller
{
    public function index(Organization $organization, Page $page, PageVersion $version): mixed
    {
        $this->assertScope($organization, $page, $version);
        $this->authorize('view', $version);

        $sections = $version->sections()->orderBy('position')->get();
        return PageSectionResource::collection($sections);
    }

    public function store(StorePageSectionRequest $request, Organization $organization, Page $page, PageVersion $version, CreatePageSection $action): JsonResponse
    {
        $this->assertScope($organization, $page, $version);
        $this->authorize('update', $version);

        $section = $action->handle((string) $version->getKey(), $request->validated());
        return (new PageSectionResource($section))->response()->setStatusCode(201);
    }

    public function show(Organization $organization, Page $page, PageVersion $version, PageSection $section): PageSectionResource
    {
        $this->assertScope($organization, $page, $version, $section);
        $this->authorize('view', $section);
        return new PageSectionResource($section);
    }

    public function update(UpdatePageSectionRequest $request, Organization $organization, Page $page, PageVersion $version, PageSection $section, UpdatePageSection $action): PageSectionResource
    {
        $this->assertScope($organization, $page, $version, $section);
        $this->authorize('update', $section);
        return new PageSectionResource($action->handle($section, $request->validated()));
    }

    public function destroy(Organization $organization, Page $page, PageVersion $version, PageSection $section, DeletePageSection $action): JsonResponse
    {
        $this->assertScope($organization, $page, $version, $section);
        $this->authorize('delete', $section);
        $action->handle($section);
        return response()->json(null, 204);
    }

    public function reorder(Request $request, Organization $organization, Page $page, PageVersion $version, ReorderPageSections $action): JsonResponse
    {
        $this->assertScope($organization, $page, $version);
        $this->authorize('update', $version);

        $validated = $request->validate([
            'section_ids' => ['required','array','min:1'],
            'section_ids.*' => ['required','uuid'],
        ]);

        $action->handle((string) $version->getKey(), $validated['section_ids']);
        return response()->json(['data' => ['updated' => true]]);
    }

    private function assertScope(Organization $organization, Page $page, PageVersion $version, ?PageSection $section = null): void
    {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        abort_unless((string) $version->page_id === (string) $page->getKey(), 404);
        if ($section) {
            abort_unless((string) $section->page_version_id === (string) $version->getKey(), 404);
        }
    }
}
