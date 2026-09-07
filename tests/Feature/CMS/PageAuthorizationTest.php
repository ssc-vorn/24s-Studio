<?php

namespace Tests\Feature\CMS;

use App\Models\Organization;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PageAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_read_a_page_from_another_organization(): void
    {
        [$user, $organizationA, $organizationB, $pageB] = $this->fixture();
        $this->grant($user, 'pages.view');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageB->id}")
            ->assertNotFound();
    }

    public function test_member_cannot_update_a_page_from_another_organization(): void
    {
        [$user, $organizationA, $organizationB, $pageB] = $this->fixture();
        $this->grant($user, 'pages.update');

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageB->id}", [
                'title' => 'Cross-org mutation',
                'slug' => $pageB->slug,
            ])
            ->assertNotFound();

        $this->assertSame('Org B page', $pageB->refresh()->title);
    }

    public function test_member_cannot_delete_a_page_from_another_organization(): void
    {
        [$user, $organizationA, $organizationB, $pageB] = $this->fixture();
        $this->grant($user, 'pages.delete');

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageB->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('pages', ['id' => $pageB->id]);
    }

    public function test_member_cannot_list_versions_through_another_organization(): void
    {
        [$user, $organizationA, $organizationB, $pageB] = $this->fixture();
        $this->grant($user, 'pages.view');
        $version = $this->version($pageB);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageB->id}/versions")
            ->assertNotFound();

        $this->assertDatabaseHas('page_versions', ['id' => $version->id]);
    }

    public function test_member_cannot_publish_a_version_through_another_organization(): void
    {
        [$user, $organizationA, $organizationB, $pageB] = $this->fixture();
        $this->grant($user, 'pages.publish');
        $version = $this->version($pageB);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageB->id}/versions/{$version->id}/publish")
            ->assertNotFound();

        $this->assertSame('draft', $version->refresh()->status);
        $this->assertSame('draft', $pageB->refresh()->status);
    }

    public function test_member_cannot_autosave_sections_through_another_organization(): void
    {
        [$user, $organizationA, $organizationB, $pageB] = $this->fixture();
        $this->grant($user, 'pages.update');
        $version = $this->version($pageB);
        $section = PageSection::query()->create([
            'page_version_id' => $version->id,
            'parent_id' => null,
            'type' => 'text',
            'variant' => null,
            'position' => 0,
            'content' => ['text' => 'Original'],
            'styles' => [],
            'responsive' => [],
            'animation' => [],
            'is_visible' => true,
        ]);

        $payload = [
            'revision' => 1,
            'sections' => [[
                'id' => $section->id,
                'parent_id' => null,
                'type' => 'text',
                'variant' => null,
                'position' => 0,
                'content' => ['text' => 'Cross-org mutation'],
                'styles' => [],
                'responsive' => [],
                'animation' => [],
                'is_visible' => true,
            ]],
        ];

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageB->id}/versions/{$version->id}/sections/autosave", $payload)
            ->assertNotFound();

        $this->assertSame('Original', $section->refresh()->content['text']);
        $this->assertSame(1, $version->refresh()->revision);
    }

    public function test_same_organization_member_with_permission_can_read_page(): void
    {
        [$user, $organizationA, $organizationB, $pageB] = $this->fixture();
        $pageA = $this->page($organizationA, 'Org A page', 'org-a-page');
        $this->grant($user, 'pages.view');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageA->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $pageA->id);
    }

    public function test_same_organization_member_without_permission_is_forbidden(): void
    {
        [$user, $organizationA] = $this->fixture();
        $pageA = $this->page($organizationA, 'Org A page', 'org-a-page');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageA->id}")
            ->assertForbidden();
    }

    /** @return array{User, Organization, Organization, Page} */
    private function fixture(): array
    {
        $user = User::factory()->create();
        $organizationA = $this->organization('org-a');
        $organizationB = $this->organization('org-b');

        $organizationA->users()->attach($user->id, ['is_owner' => false]);

        return [$user, $organizationA, $organizationB, $this->page($organizationB, 'Org B page', 'org-b-page')];
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

    private function version(Page $page): PageVersion
    {
        return PageVersion::query()->create([
            'page_id' => $page->id,
            'version' => 1,
            'status' => 'draft',
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
