<?php

namespace App\Domain\CMS\Actions;

use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Support\Facades\DB;

final class CreatePageVersion
{
    /** @param array<string,mixed> $content */
    public function handle(Page $page, array $content, int $userId): PageVersion
    {
        return DB::transaction(function () use ($page, $content, $userId): PageVersion {
            $nextVersion = ((int) $page->versions()->max('version')) + 1;

            return $page->versions()->create([
                'version' => $nextVersion,
                'status' => 'draft',
                'content' => $content,
                'created_by' => $userId,
                'revision' => 1,
            ]);
        });
    }
}
