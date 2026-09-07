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

class PublishPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_an_approved_version_can_be_published(): void
    {
        [$user, $organization, $page] = $this->fixture();
        $this->grant($user, 'pages.publish');
        $version = PageVersion::query()->create([
            'page_id' => $page->id,
            'version' => 1,
            'status' => 'draft',
            'revision' => 1,
            'content' => [],
        ]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/organizations/{$organization->id}/pages/{$page->id}/versions/{$version->id}/publish")
            ->assertStatus(422);

        $this->assertSame('draft', $version->refresh()->status);
        $this->assertSame('draft', $page->refresh()->status);
    }

    public function test_publishing_an_approved_version_replaces_the_previous_published_version(): void
    {
        [$user, $organization, $page] = $this->fixture();
        $this->grant($user, 'pages.publish');
        $old = PageVersion::query()->create([
            'page_id' => $page->id,
            'version' => 1,
            'status' => 'published',
            'revision' => 1,
            'content' => ['title' => 'old'],
            'published_at' => now()->subDay(),
        ]);
        $next = PageVersion::query()->create([
            'page_id' => $page->id,
            'version' => 2,
            'status' => 'approved',
            'revision' => 1,
            'content' => ['title' => 'new'],
        ]);
        $page->update(['status' => 'published']);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/organizations/{$organization->id}/pages/{$page->id}/versions/{$next->id}/publish")
            ->assertOk()
            ->assertJsonPath('data.id', $page->id);

        $this->assertSame('approved', $old->refresh()->status);
        $this->assertSame('published', $next->refresh()->status);
        $this->assertNotNull($next->published_at);
        $this->assertSame('published', $page->refresh()->status);
    }

    private function fixture(): array
    {
        $user = User::factory()->create();
        $organization = Organization::query()->create([
            'name' => 'Publish Test',
            'slug' => 'publish-test-'.Str::lower(Str::random(6)),
            'status' => 'active',
            'settings' => [],
        ]);
        $organization->users()->attach($user->id, ['is_owner' => false]);
        $page = Page::query()->create([
            'organization_id' => $organization->id,
            'title' => 'Publish Test Page',
            'slug' => 'publish-test-'.Str::lower(Str::random(6)),
            'status' => 'draft',
            'template' => 'default',
            'is_homepage' => false,
            'metadata' => [],
        ]);

        return [$user, $organization, $page];
    }

    private function grant(User $user, string $permission): void
    {
        Permission::findOrCreate($permission, 'web');
        $user->givePermissionTo($permission);
    }
}
