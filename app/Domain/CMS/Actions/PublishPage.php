<?php

namespace App\Domain\CMS\Actions;

use App\Domain\Audit\AuditLogger;
use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Support\Facades\DB;

final class PublishPage
{
    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function handle(Page $page, PageVersion $version): Page
    {
        return DB::transaction(function () use ($page, $version): Page {
            abort_unless((string) $version->page_id === (string) $page->getKey(), 422, 'The version does not belong to this page.');

            $lockedVersion = PageVersion::query()->whereKey($version->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($lockedVersion->status === 'approved', 422, 'Only approved versions can be published.');

            $previousPublished = $page->versions()
                ->where('status', 'published')
                ->whereKeyNot($lockedVersion->getKey())
                ->get(['id', 'version', 'status', 'published_at']);

            $page->versions()
                ->where('status', 'published')
                ->whereKeyNot($lockedVersion->getKey())
                ->update(['status' => 'approved', 'published_at' => null]);

            $before = [
                'page_status' => $page->status,
                'version_status' => $lockedVersion->status,
                'version_id' => $lockedVersion->getKey(),
                'previous_published_versions' => $previousPublished->toArray(),
            ];

            $lockedVersion->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            $page->update(['status' => 'published']);
            $page->refresh();
            $lockedVersion->refresh();

            $this->auditLogger->log(
                action: 'page.version.published',
                auditable: $lockedVersion,
                before: $before,
                after: [
                    'page_status' => $page->status,
                    'version_status' => $lockedVersion->status,
                    'version_id' => $lockedVersion->getKey(),
                    'published_at' => $lockedVersion->published_at?->toISOString(),
                ],
                organizationId: (string) $page->organization_id,
            );

            return $page;
        });
    }
}
