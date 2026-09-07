<?php

namespace App\Domain\CMS\Actions;

use App\Domain\CMS\DTOs\PageData;
use App\Models\Page;
use Illuminate\Support\Facades\DB;

final class CreatePage
{
    public function handle(PageData $data, int $userId): Page
    {
        return DB::transaction(function () use ($data, $userId): Page {
            return Page::query()->create([
                'organization_id' => $data->organizationId,
                'title' => $data->title,
                'slug' => $data->slug,
                'status' => $data->status,
                'template' => $data->template,
                'is_homepage' => $data->isHomepage,
                'metadata' => $data->metadata,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        });
    }
}
