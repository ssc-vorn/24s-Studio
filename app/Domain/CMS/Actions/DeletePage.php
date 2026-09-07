<?php

namespace App\Domain\CMS\Actions;

use App\Models\Page;
use Illuminate\Support\Facades\DB;

final class DeletePage
{
    public function handle(Page $page): void
    {
        DB::transaction(static function () use ($page): void {
            $page->delete();
        });
    }
}
