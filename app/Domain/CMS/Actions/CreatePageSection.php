<?php

namespace App\Domain\CMS\Actions;

use App\Models\PageSection;
use Illuminate\Support\Facades\DB;

final class CreatePageSection
{
    /** @param array<string,mixed> $data */
    public function handle(string $pageVersionId, array $data): PageSection
    {
        return DB::transaction(function () use ($pageVersionId, $data): PageSection {
            $position = array_key_exists('position', $data)
                ? (int) $data['position']
                : ((int) PageSection::query()->where('page_version_id', $pageVersionId)->max('position')) + 1;

            return PageSection::query()->create([
                'page_version_id' => $pageVersionId,
                'parent_id' => $data['parent_id'] ?? null,
                'type' => $data['type'],
                'variant' => $data['variant'] ?? null,
                'position' => $position,
                'content' => $data['content'] ?? [],
                'styles' => $data['styles'] ?? [],
                'responsive' => $data['responsive'] ?? [],
                'animation' => $data['animation'] ?? [],
                'visibility' => $data['visibility'] ?? true,
            ]);
        });
    }
}
