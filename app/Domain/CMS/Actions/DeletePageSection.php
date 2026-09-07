<?php

namespace App\Domain\CMS\Actions;

use App\Models\PageSection;
use Illuminate\Support\Facades\DB;

final class DeletePageSection
{
    public function handle(PageSection $section): void
    {
        DB::transaction(function () use ($section): void {
            PageSection::query()
                ->where('parent_id', $section->getKey())
                ->update(['parent_id' => $section->parent_id]);

            $section->delete();
        });
    }
}
