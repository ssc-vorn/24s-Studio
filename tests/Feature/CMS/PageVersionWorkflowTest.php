<?php

namespace Tests\Feature\CMS;

use App\Models\AuditLog;
use App\Models\Organization;
use App\Models\Page;
use App\Models\PageVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PageVersionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_submit_draft_for_review(): void
    {
        [$user, $organization, $page, $version] = $this->fixture();
        $this->grant($user, 'pages.update');

        $this->actingAs($user, 'sanctum')
            ->postJson($this->url($organization, $page, $version, 'submit-review'))
            ->assertOk()
            ->assertJsonPath('data.status', 'review');

        $this->assertSame('review', $version->refresh()->status);

        $audit = AuditLog::query()->latest('id')->firstOrFail();
        $this->assertSame('page.version.submitted_for_review', $audit->action);
        $this->assertSame($organization->id, $audit->organization_id);
        $this->assertSame($user->id, $audit->user_id);
        $this->assertSame((string) $version->id, (string) $audit->auditable_id);
        $this->assertSame(['status' => 'draft'], $audit->before_data);
        $this->assertSame(['status' => 'review'], $audit->after_data);
    }

    public function test_member_with_publish_permission_can_approve_review_version(): void
    {
        [$user, $organization, $page, $version] = $this->fixture('review');
        $this->grant($user, 'pages.publish');

        $this->actingAs($user, 'sanctum')
            ->postJson($this->url($organization, $page, $version, 'approve'))
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertSame('approved', $version->refresh()->status);

        $audit = AuditLog::query()->latest('id')->firstOrFail();
        $this->assertSame('page.version.approved', $audit->action);
        $this->assertSame(['status' => 'review'], $audit->before_data);
        $this->assertSame(['status' => 'approved'], $audit->after_data);
    }

    public function test_invalid_transition_is_rejected_without_audit_log(): void
    {
        [$user, $organization, $page, $version] = $this->fixture('draft');
        $this->grant($user, 'pages.publish');

        $this->actingAs($user, 'sanctum')
            ->postJson($this->url($organization, $page, $version, 'approve'))
            ->assertUnprocessable();

        $this->assertSame('draft', $version->refresh()->status);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_cross_organization_transition_is_not_found(): void
    {
        [$user, $organization, $page, $version] = $this->fixture();
        $otherOrganization = $this->organization('org-other');
        $this->grant($user, 'pages.update');

        $this->actingAs($user, 'sanctum')
            ->postJson($this->url($otherOrganization, $page, $version, 'submit-review'))
            ->assertNotFound();

        $this->assertSame('draft', $version->refresh()->status);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    private function url(Organization $organization, Page $page, PageVersion $version, string $action): string
    {
        return "/api/v1/organizations/{$organization->id}/pages/{$page->id}/versions/{$version->id}/{$action}";
    }

    /** @return array{User, Organization, Page, PageVersion} */
    private function fixture(string $status = 'draft'): array
    {
        $user = User::factory()->create();
        $organization = $this->organization('org-a');
        $organization->users()->attach($user->id, ['is_owner' => false]);
        $page = Page::query()->create([
            'organization_id' => $organization->id,
            'title' => 'Workflow page',
            'slug' => 'workflow-page',
            'status' => 'draft',
            'template' => 'default',
            'is_homepage' => false,
            'metadata' => [],
        ]);
        $version = PageVersion::query()->create([
            'page_id' => $page->id,
            'version' => 1,
            'status' => $status,
            'revision' => 1,
            'content' => [],
        ]);

        return [$user, $organization, $page, $version];
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

    private function grant(User $user, string $permission): void
    {
        Permission::findOrCreate($permission, 'web');
        $user->givePermissionTo($permission);
    }
}
