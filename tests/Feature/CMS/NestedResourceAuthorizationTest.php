<?php

namespace Tests\Feature\CMS;

use App\Models\Media;
use App\Models\Organization;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NestedResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_cannot_list_sections_from_another_organization(): void
    {
        [$user, $organizationA, $pageB, $versionB, $sectionB] = $this->sectionFixture();
        $this->grant($user, 'pages.view');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageB->id}/versions/{$versionB->id}/sections")
            ->assertNotFound();
    }

    public function test_member_cannot_show_update_or_delete_another_organizations_section(): void
    {
        [$user, $organizationA, $pageB, $versionB, $sectionB] = $this->sectionFixture();
        $this->grant($user, 'pages.view');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageB->id}/versions/{$versionB->id}/sections/{$sectionB->id}")
            ->assertNotFound();

        $this->grant($user, 'pages.update');
        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageB->id}/versions/{$versionB->id}/sections/{$sectionB->id}", [
                'type' => 'text',
                'position' => 0,
                'content' => ['text' => 'mutated'],
                'styles' => [],
                'responsive' => [],
                'animation' => [],
                'is_visible' => true,
            ])
            ->assertNotFound();

        $this->grant($user, 'pages.delete');
        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageB->id}/versions/{$versionB->id}/sections/{$sectionB->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('page_sections', ['id' => $sectionB->id]);
    }

    public function test_member_cannot_reorder_sections_from_another_organization(): void
    {
        [$user, $organizationA, $pageB, $versionB, $sectionB] = $this->sectionFixture();
        $this->grant($user, 'pages.update');

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageB->id}/versions/{$versionB->id}/sections/reorder", [
                'section_ids' => [$sectionB->id],
            ])
            ->assertNotFound();

        $this->assertSame(0, $sectionB->refresh()->position);
    }

    public function test_member_cannot_list_media_from_another_organization(): void
    {
        [$user, $organizationA, $organizationB] = $this->orgFixture();
        $this->grant($user, 'media.view');
        $mediaB = $this->media($organizationB);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/organizations/{$organizationB->id}/media")
            ->assertForbidden();

        $this->assertDatabaseHas('media', ['id' => $mediaB->id]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/organizations/{$organizationA->id}/media")
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_member_cannot_update_or_delete_media_from_another_organization(): void
    {
        [$user, $organizationA, $organizationB] = $this->orgFixture();
        $mediaB = $this->media($organizationB);
        $this->grant($user, 'media.update');

        $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/organizations/{$organizationA->id}/media/{$mediaB->id}", [
                'alt' => 'mutated',
            ])
            ->assertNotFound();

        $this->grant($user, 'media.delete');
        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/organizations/{$organizationA->id}/media/{$mediaB->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('media', ['id' => $mediaB->id]);
        $this->assertSame('Original', $mediaB->refresh()->alt);
    }

    public function test_member_can_access_nested_resources_in_their_organization(): void
    {
        [$user, $organizationA, $pageA, $versionA, $sectionA] = $this->sectionFixture(true);
        $this->grant($user, 'pages.view');

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/organizations/{$organizationA->id}/pages/{$pageA->id}/versions/{$versionA->id}/sections/{$sectionA->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $sectionA->id);
    }

    private function sectionFixture(bool $sameOrganization = false): array
    {
        [$user, $organizationA, $organizationB] = $this->orgFixture();
        $page = $this->page($sameOrganization ? $organizationA : $organizationB);
        $version = $this->version($page);
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

        return [$user, $sameOrganization ? $organizationA : $organizationA, $page, $version, $section];
    }

    private function orgFixture(): array
    {
        $user = User::factory()->create();
        $organizationA = $this->organization('org-a-' . uniqid());
        $organizationB = $this->organization('org-b-' . uniqid());
        $organizationA->users()->attach($user->id, ['is_owner' => false]);

        return [$user, $organizationA, $organizationB];
    }

    private function organization(string $slug): Organization
    {
        return Organization::query()->create([
            'name' => $slug,
            'slug' => $slug,
            'status' => 'active',
            'settings' => [],
        ]);
    }

    private function page(Organization $organization): Page
    {
        return Page::query()->create([
            'organization_id' => $organization->id,
            'title' => 'Nested page',
            'slug' => 'nested-' . uniqid(),
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

    private function media(Organization $organization): Media
    {
        return Media::query()->create([
            'organization_id' => $organization->id,
            'path' => 'organizations/' . $organization->id . '/media/test.txt',
            'filename' => 'test.txt',
            'mime_type' => 'text/plain',
            'size' => 7,
            'alt' => 'Original',
            'metadata' => [],
        ]);
    }

    private function grant(User $user, string $permission): void
    {
        Permission::findOrCreate($permission, 'web');
        $user->givePermissionTo($permission);
    }
}
