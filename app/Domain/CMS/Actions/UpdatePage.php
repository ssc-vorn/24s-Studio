<?php

namespace App\Domain\CMS\Actions;

use App\Domain\CMS\DTOs\PageData;
use App\Models\Page;
use Illuminate\Support\Facades\DB;

final class UpdatePage
{
    public function handle(Page $page, PageData $data, int $userId): Page
    {
        return DB::transaction(function () use ($page, $data, $userId): Page {
            $page->fill([
                'title' => $data->title,
                'slug' => $data->slug,
                'status' => $data->status,
                'template' => $data->template,
                'is_homepage' => $data->isHomepage,
                'metadata' => $data->metadata,
                'updated_by' => $userId,
            ])->save();

            return $page->refresh();
        });
    }
}
