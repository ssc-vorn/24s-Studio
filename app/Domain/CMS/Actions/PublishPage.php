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

            $page->versions()->where('status', 'published')->update(['status' => 'approved']);
            $version->update(['status' => 'published', 'published_at' => now()]);
            $page->update(['status' => 'published']);

            return $page->refresh();
        });
    }
}
