<?php

namespace App\Domain\CMS\Actions;

use App\Models\PageSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class CreatePageSection
{
    /** @param array<string,mixed> $data */
    public function handle(string $pageVersionId, array $data): PageSection
    {
        return DB::transaction(function () use ($pageVersionId, $data): PageSection {
            $parentId = $data['parent_id'] ?? null;

            if ($parentId !== null) {
                $parent = PageSection::query()->find($parentId);
                if (!$parent || (string) $parent->page_version_id !== $pageVersionId) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'The parent section must belong to the selected page version.',
                    ]);
                }
            }

            $position = array_key_exists('position', $data)
                ? (int) $data['position']
                : ((int) PageSection::query()
                    ->where('page_version_id', $pageVersionId)
                    ->where('parent_id', $parentId)
                    ->max('position')) + 1;

            return PageSection::query()->create([
                'page_version_id' => $pageVersionId,
                'parent_id' => $parentId,
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
