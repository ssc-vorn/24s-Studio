<?php

namespace App\Http\Controllers\Api\V1\CMS;

use App\Domain\CMS\Actions\AutosavePageSections;
use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\AutosavePageSectionsRequest;
use App\Models\Organization;
use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Http\JsonResponse;

class PageSectionAutosaveController extends Controller
{
    public function __invoke(
        AutosavePageSectionsRequest $request,
        Organization $organization,
        Page $page,
        PageVersion $version,
        AutosavePageSections $action,
    ): JsonResponse {
        abort_unless((string) $page->organization_id === (string) $organization->getKey(), 404);
        abort_unless((string) $version->page_id === (string) $page->getKey(), 404);
        $this->authorize('update', $version);

        $revision = $action->handle(
            $version,
            (int) $request->validated('revision'),
            $request->validated('sections'),
        );

        return response()->json(['data' => ['revision' => $revision]]);
    }
}
