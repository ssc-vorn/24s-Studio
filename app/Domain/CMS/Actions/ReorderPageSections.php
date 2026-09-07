<?php

namespace App\Domain\CMS\Actions;

use App\Models\PageSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ReorderPageSections
{
    /** @param array<int,string> $sectionIds */
    public function handle(string $pageVersionId, array $sectionIds): void
    {
        DB::transaction(function () use ($pageVersionId, $sectionIds): void {
            $sections = PageSection::query()
                ->where('page_version_id', $pageVersionId)
                ->whereIn('id', $sectionIds)
                ->get(['id']);

            if ($sections->count() !== count(array_unique($sectionIds))) {
                throw ValidationException::withMessages([
                    'sections' => 'Every section must belong to the selected page version.',
                ]);
            }

            foreach (array_values($sectionIds) as $position => $sectionId) {
                PageSection::query()
                    ->whereKey($sectionId)
                    ->update(['position' => $position]);
            }
        });
    }
}
