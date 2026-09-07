<?php

namespace App\Domain\CMS\Actions;

use App\Models\PageSection;
use Illuminate\Support\Facades\DB;

final class UpdatePageSection
{
    /** @param array<string,mixed> $data */
    public function handle(PageSection $section, array $data): PageSection
    {
        return DB::transaction(function () use ($section, $data): PageSection {
            $section->fill($data);
            $section->save();
            return $section->refresh();
        });
    }
}
