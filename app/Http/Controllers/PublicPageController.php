<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PublicPageController extends Controller
{
    public function home(): InertiaResponse
    {
        $page = Page::query()
            ->where('is_homepage', true)
            ->where('status', 'published')
            ->firstOrFail();

        return $this->render($page);
    }

    public function show(string $slug): InertiaResponse
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return $this->render($page);
    }

    private function render(Page $page): InertiaResponse
    {
        $version = $page->versions()
            ->where('status', 'published')
            ->latest('published_at')
            ->latest('version')
            ->firstOrFail();

        $sections = $version->sections()
            ->where('is_visible', true)
            ->orderBy('position')
            ->get()
            ->values();

        return Inertia::render('Public/CmsPage', [
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'template' => $page->template,
                'metadata' => $page->metadata ?? [],
            ],
            'version' => [
                'id' => $version->id,
                'version' => (int) $version->version,
                'published_at' => $version->published_at?->toISOString(),
            ],
            'sections' => $sections,
        ]);
    }
}
