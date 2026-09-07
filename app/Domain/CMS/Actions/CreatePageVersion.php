<?php

namespace App\Domain\CMS\Actions;

use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Support\Facades\DB;

final class CreatePageVersion
{
    /** @param array<string,mixed> $content */
    public function handle(Page $page, array $content, int $userId, string $status = 'draft'): PageVersion
    {
        return DB::transaction(function () use ($page, $content, $userId, $status): PageVersion {
            $nextVersion = ((int) $page->versions()->max('version')) + 1;

            return $page->versions()->create([
                'version' => $nextVersion,
                'status' => $status,
                'content' => $content,
                'created_by' => $userId,
            ]);
        });
    }
}
