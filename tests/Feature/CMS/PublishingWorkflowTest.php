<?php

namespace Tests\Feature\CMS;

use App\Models\Organization;
use App\Models\Page;
use App\Models\PageVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PublishingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_draft_version_cannot_be_published(): void
    {
        [$user, $organization, $page] = $this->fixture();
        $this->grant($user, 'pages.publish');
        $version = $this->version($page, 'draft');

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/organizations/{$organization->id}/pages/{$page->id}/versions/{$version->id}/publish")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Only approved versions can be published.');

        $this->assertSame('draft', $version->refresh()->status);
        $this->assertSame('draft', $page->refresh()->status);
    }

    public function test_approved_version_can_be_published_and_replaces_previous_published_version(): void
    {
        [$user, $organization, $page] = $this->fixture();
        $this->grant($user, 'pages.publish');
        $old = $this->version($page, 'published');
        $old->update(['published_at' => now()->subMinute()]);
        $new = $this->version($page, 'approved', 2);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/organizations/{$organization->id}/pages/{$page->id}/versions/{$new->id}/publish")
            ->assertOk();

        $this->assertSame('approved', $old->refresh()->status);
        $this->assertNull($old->published_at);
        $this->assertSame('published', $new->refresh()->status);
        $this->assertNotNull($new->published_at);
        $this->assertSame('published', $page->refresh()->status);
    }

    public function test_public_page_does_not_expose_draft_page(): void
    {
        $organization = $this->organization('draft-org');
        $page = $this->page($organization, 'Draft page', 'draft-page');
        $this->version($page, 'draft');

        $this->get('/pages/draft-page')->assertNotFound();
    }

    public function test_public_page_renders_only_published_version(): void
    {
        $organization = $this->organization('published-org');
        $page = $this->page($organization, 'Published page', 'published-page');
        $version = $this->version($page, 'published');
        $version->update(['published_at' => now()]);

        $this->get('/pages/published-page')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/CmsPage')
                ->where('page.slug', 'published-page')
                ->where('version.version', 1)
            );
    }

    private function fixture(): array
    {
        $user = User::factory()->create();
        $organization = $this->organization('workflow-org');
        $organization->users()->attach($user->id, ['is_owner' => false]);
        return [$user, $organization, $this->page($organization, 'Workflow page', 'workflow-page')];
    }

    private function organization(string $slug): Organization
    {
        return Organization::query()->create([
            'name' => Str::headline($slug),
            'slug' => $slug,
            'status' => 'active',
            'settings' => [],
        ]);
    }

    private function page(Organization $organization, string $title, string $slug): Page
    {
        return Page::query()->create([
            'organization_id' => $organization->id,
            'title' => $title,
            'slug' => $slug,
            'status' => 'draft',
            'template' => 'default',
            'is_homepage' => false,
            'metadata' => [],
        ]);
    }

    private function version(Page $page, string $status, int $number = 1): PageVersion
    {
        return PageVersion::query()->create([
            'page_id' => $page->id,
            'version' => $number,
            'status' => $status,
            'revision' => 1,
            'content' => [],
        ]);
    }

    private function grant(User $user, string $permission): void
    {
        Permission::findOrCreate($permission, 'web');
        $user->givePermissionTo($permission);
    }
}
