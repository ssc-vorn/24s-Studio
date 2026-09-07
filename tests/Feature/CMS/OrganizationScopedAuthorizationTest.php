<?php

namespace Tests\Feature\CMS;

use App\Models\Organization;
use App\Models\User;
use App\Support\Tenancy\OrganizationAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrganizationScopedAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_permissions_are_limited_to_the_organization_membership_role(): void
    {
        $user = User::factory()->create();
        $organizationA = $this->organization('scope-a');
        $organizationB = $this->organization('scope-b');
        $organizationA->users()->attach($user->id, ['is_owner' => false, 'role' => 'editor']);
        $organizationB->users()->attach($user->id, ['is_owner' => false, 'role' => 'viewer']);

        $access = app(OrganizationAccess::class);

        $this->assertTrue($access->can($user, $organizationA->id, 'pages.update'));
        $this->assertFalse($access->can($user, $organizationB->id, 'pages.update'));
        $this->assertTrue($access->can($user, $organizationB->id, 'pages.view'));
    }

    public function test_reviewer_can_approve_but_cannot_publish(): void
    {
        $user = User::factory()->create();
        $organization = $this->organization('reviewer');
        $organization->users()->attach($user->id, ['is_owner' => false, 'role' => 'reviewer']);

        $access = app(OrganizationAccess::class);

        $this->assertTrue($access->can($user, $organization->id, 'pages.approve'));
        $this->assertFalse($access->can($user, $organization->id, 'pages.publish'));
    }

    public function test_organization_owner_has_full_access(): void
    {
        $user = User::factory()->create();
        $organization = $this->organization('owner');
        $organization->users()->attach($user->id, ['is_owner' => true, 'role' => 'viewer']);

        $this->assertTrue(app(OrganizationAccess::class)->can($user, $organization->id, 'pages.publish'));
    }

    private function organization(string $slug): Organization
    {
        return Organization::query()->create([
            'name' => Str::headline($slug),
            'slug' => $slug.'-'.Str::lower(Str::random(6)),
            'status' => 'active',
            'settings' => [],
        ]);
    }
}
