<?php

namespace App\Domain\CMS\Actions;

use App\Models\PageSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class UpdatePageSection
{
    /** @param array<string,mixed> $data */
    public function handle(PageSection $section, array $data): PageSection
    {
        return DB::transaction(function () use ($section, $data): PageSection {
            if (array_key_exists('parent_id', $data)) {
                $parentId = $data['parent_id'];
                if ($parentId === $section->getKey()) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'A section cannot be its own parent.',
                    ]);
                }

                if ($parentId !== null) {
                    $parent = PageSection::query()->find($parentId);
                    if (!$parent || (string) $parent->page_version_id !== (string) $section->page_version_id) {
                        throw ValidationException::withMessages([
                            'parent_id' => 'The parent section must belong to the selected page version.',
                        ]);
                    }

                    $ancestorId = $parent->parent_id;
                    while ($ancestorId !== null) {
                        if ((string) $ancestorId === (string) $section->getKey()) {
                            throw ValidationException::withMessages([
                                'parent_id' => 'A section cannot be moved inside its own descendant.',
                            ]);
                        }
                        $ancestorId = PageSection::query()->whereKey($ancestorId)->value('parent_id');
                    }
                }
            }

            $section->fill($data);
            $section->save();
            return $section->refresh();
        });
    }
}
