<?php

namespace App\Domain\CMS\Actions;

use App\Domain\Audit\AuditLogger;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class RestorePageVersion
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function handle(Page $page, PageVersion $source, int $userId): PageVersion
    {
        return DB::transaction(function () use ($page, $source, $userId): PageVersion {
            abort_unless((string) $source->page_id === (string) $page->getKey(), 404);

            $source->loadMissing('sections');
            $nextVersion = ((int) $page->versions()->max('version')) + 1;

            $version = $page->versions()->create([
                'version' => $nextVersion,
                'status' => 'draft',
                'revision' => 1,
                'content' => $source->content ?? [],
                'created_by' => $userId,
            ]);

            $idMap = [];
            foreach ($source->sections as $section) {
                $idMap[$section->getKey()] = (string) Str::uuid();
            }

            foreach ($source->sections as $section) {
                PageSection::query()->create([
                    'id' => $idMap[$section->getKey()],
                    'page_version_id' => $version->getKey(),
                    'parent_id' => $section->parent_id ? ($idMap[$section->parent_id] ?? null) : null,
                    'type' => $section->type,
                    'variant' => $section->variant,
                    'position' => $section->position,
                    'content' => $section->content ?? [],
                    'styles' => $section->styles ?? [],
                    'responsive' => $section->responsive ?? [],
                    'animation' => $section->animation ?? [],
                    'is_visible' => (bool) $section->is_visible,
                ]);
            }

            $this->auditLogger->log(
                action: 'page.version.restored',
                auditable: $version,
                before: ['source_version_id' => $source->getKey(), 'source_version' => (int) $source->version],
                after: ['version_id' => $version->getKey(), 'version' => (int) $version->version, 'status' => $version->status],
                organizationId: (string) $page->organization_id,
            );

            return $version->load('sections');
        });
    }
}
