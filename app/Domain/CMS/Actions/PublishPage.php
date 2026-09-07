<?php

namespace App\Domain\CMS\Actions;

use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Support\Facades\DB;

final class PublishPage
{
    public function handle(Page $page, PageVersion $version): Page
    {
        return DB::transaction(function () use ($page, $version): Page {
            abort_unless((string) $version->page_id === (string) $page->getKey(), 422, 'The version does not belong to this page.');

            $lockedVersion = PageVersion::query()->whereKey($version->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($lockedVersion->status === 'approved', 422, 'Only approved versions can be published.');

            $page->versions()
                ->where('status', 'published')
                ->whereKeyNot($lockedVersion->getKey())
                ->update(['status' => 'approved', 'published_at' => null]);

            $lockedVersion->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            $page->update(['status' => 'published']);

            return $page->refresh();
        });
    }
}
