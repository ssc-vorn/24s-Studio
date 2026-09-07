<?php

namespace App\Domain\CMS\Actions;

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

    public function handle(Page $page, PageVersion $version, string $transition): PageVersion
    {
        $target = self::TRANSITIONS[$transition] ?? null;
        abort_unless($target !== null, 422, 'Unsupported version transition.');

        return DB::transaction(function () use ($page, $version, $target): PageVersion {
            abort_unless((string) $version->page_id === (string) $page->getKey(), 404);

            $lockedVersion = PageVersion::query()
                ->whereKey($version->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $expected = $target === 'review' ? 'draft' : 'review';
            abort_unless($lockedVersion->status === $expected, 422, "Only {$expected} versions can move to {$target}.");

            $lockedVersion->update([
                'status' => $target,
            ]);

            return $lockedVersion->refresh();
        });
    }
}
