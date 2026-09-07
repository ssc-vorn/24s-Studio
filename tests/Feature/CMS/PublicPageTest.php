<?php

namespace Tests\Feature\CMS;

use App\Models\Organization;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unpublished_page_is_not_publicly_visible(): void
    {
        $page = $this->page('draft-page', false);

        $this->get('/pages/'.$page->slug)->assertNotFound();
    }

    public function test_published_page_renders_only_its_published_version_and_visible_sections(): void
    {
        $page = $this->page('published-page', false);
        $published = PageVersion::query()->create([
            'page_id' => $page->id,
            'version' => 1,
            'status' => 'published',
            'revision' => 1,
            'content' => ['source' => 'published'],
            'published_at' => now(),
        ]);
        PageVersion::query()->create([
            'page_id' => $page->id,
            'version' => 2,
            'status' => 'draft',
            'revision' => 1,
            'content' => ['source' => 'draft'],
        ]);
        $visible = PageSection::query()->create([
            'page_version_id' => $published->id,
            'type' => 'text',
            'position' => 0,
            'content' => ['title' => 'Visible section'],
            'styles' => [],
            'responsive' => [],
            'animation' => [],
            'is_visible' => true,
        ]);
        PageSection::query()->create([
            'page_version_id' => $published->id,
            'type' => 'text',
            'position' => 1,
            'content' => ['title' => 'Hidden section'],
            'styles' => [],
            'responsive' => [],
            'animation' => [],
            'is_visible' => false,
        ]);
        $page->update(['status' => 'published']);

        $this->get('/pages/'.$page->slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('version.id', $published->id)
                ->where('sections.0.id', $visible->id)
                ->missing('sections.1'));
    }

    private function page(string $slug, bool $homepage): Page
    {
        $organization = Organization::query()->create([
            'name' => 'Public Test',
            'slug' => 'public-test-'.Str::lower(Str::random(6)),
            'status' => 'active',
            'settings' => [],
        ]);

        return Page::query()->create([
            'organization_id' => $organization->id,
            'title' => Str::headline($slug),
            'slug' => $slug.'-'.Str::lower(Str::random(6)),
            'status' => 'draft',
            'template' => 'default',
            'is_homepage' => $homepage,
            'metadata' => [],
        ]);
    }
}
