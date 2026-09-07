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

class DashboardPageManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_open_the_dashboard_for_an_organization(): void
    {
        [$user, $organization] = $this->fixture();
        $this->grant($user, 'pages.view');

        $this->actingAs($user)
            ->get('/dashboard?organization='.$organization->id)
            ->assertOk();
    }

    public function test_member_with_create_permission_can_create_a_page_and_its_initial_draft(): void
    {
        [$user, $organization] = $this->fixture();
        $this->grant($user, 'pages.create');

        $response = $this->actingAs($user)
            ->post("/admin/cms/organizations/{$organization->id}/pages", [
                'title' => 'About our team',
                'slug' => 'about-our-team',
                'template' => 'default',
            ]);

        $page = Page::query()->where('organization_id', $organization->id)->sole();
        $version = PageVersion::query()->where('page_id', $page->id)->sole();

        $response->assertRedirect("/admin/cms/organizations/{$organization->id}/pages/{$page->id}/builder/{$version->id}");
        $this->assertSame('About our team', $page->title);
        $this->assertSame('draft', $version->status);
        $this->assertSame(1, $version->version);
    }

    public function test_member_without_create_permission_cannot_create_a_page(): void
    {
        [$user, $organization] = $this->fixture();

        $this->actingAs($user)
            ->post("/admin/cms/organizations/{$organization->id}/pages", [
                'title' => 'Unauthorized page',
                'slug' => 'unauthorized-page',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('pages', 0);
    }

    /** @return array{User, Organization} */
    private function fixture(): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $organization = Organization::query()->create([
            'name' => 'Dashboard Test',
            'slug' => 'dashboard-test-'.Str::lower(Str::random(6)),
            'status' => 'active',
            'settings' => [],
        ]);
        $organization->users()->attach($user->id, ['is_owner' => false]);

        return [$user, $organization];
    }

    private function grant(User $user, string $permission): void
    {
        Permission::findOrCreate($permission, 'web');
        $user->givePermissionTo($permission);
    }
}
