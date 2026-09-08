<?php

namespace App\Domain\CMS\Actions;

use App\Domain\Audit\AuditLogger;
use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Support\Facades\DB;

final class TransitionPageVersionStatus
{
    /** @var array<string,string> */
    private const TRANSITIONS = [
        'submit-review' => 'review',
        'approve' => 'approved',
    ];

    public function __construct(private readonly AuditLogger $auditLogger)
    {
    }

    public function handle(Page $page, PageVersion $version, string $transition): PageVersion
    {
        $target = self::TRANSITIONS[$transition] ?? null;
        abort_unless($target !== null, 422, 'Unsupported version transition.');

        return DB::transaction(function () use ($page, $version, $target, $transition): PageVersion {
            abort_unless((string) $version->page_id === (string) $page->getKey(), 404);

            $lockedVersion = PageVersion::query()
                ->whereKey($version->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $expected = $target === 'review' ? 'draft' : 'review';
            abort_unless($lockedVersion->status === $expected, 422, "Only {$expected} versions can move to {$target}.");

            $before = ['status' => $lockedVersion->status];
            $lockedVersion->update(['status' => $target]);
            $lockedVersion->refresh();

            $this->auditLogger->log(
                action: $transition === 'submit-review'
                    ? 'page.version.submitted_for_review'
                    : 'page.version.approved',
                auditable: $lockedVersion,
                before: $before,
                after: ['status' => $lockedVersion->status],
                organizationId: (string) $page->organization_id,
            );

            return $lockedVersion;
        });
    }
}
