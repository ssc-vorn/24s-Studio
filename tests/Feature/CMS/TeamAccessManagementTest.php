<?php

namespace Tests\Feature\CMS;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TeamAccessManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_an_existing_user_with_an_organization_role(): void
    {
        [$owner, $organization] = $this->ownerFixture();
        $member = User::factory()->create(['email' => 'editor@example.com']);

        $this->actingAs($owner)
            ->post("/admin/cms/organizations/{$organization->id}/team", ['email' => $member->email, 'role' => 'editor'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('organization_user', [
            'organization_id' => $organization->id,
            'user_id' => $member->id,
            'role' => 'editor',
            'is_owner' => false,
        ]);
    }

    public function test_owner_can_change_a_non_owner_member_role(): void
    {
        [$owner, $organization] = $this->ownerFixture();
        $member = User::factory()->create();
        $organization->users()->attach($member->id, ['is_owner' => false, 'role' => 'viewer']);

        $this->actingAs($owner)
            ->patch("/admin/cms/organizations/{$organization->id}/team/{$member->id}", ['role' => 'reviewer'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('organization_user', ['organization_id' => $organization->id, 'user_id' => $member->id, 'role' => 'reviewer']);
    }

    public function test_viewer_cannot_manage_team_access(): void
    {
        [$owner, $organization] = $this->ownerFixture();
        $viewer = User::factory()->create();
        $organization->users()->attach($viewer->id, ['is_owner' => false, 'role' => 'viewer']);

        $this->actingAs($viewer)
            ->get("/admin/cms/organizations/{$organization->id}/team")
            ->assertForbidden();
    }

    /** @return array{User, Organization} */
    private function ownerFixture(): array
    {
        $owner = User::factory()->create();
        $organization = Organization::query()->create([
            'name' => 'Team access test',
            'slug' => 'team-access-'.Str::lower(Str::random(6)),
            'status' => 'active',
            'settings' => [],
        ]);
        $organization->users()->attach($owner->id, ['is_owner' => true, 'role' => 'owner']);

        return [$owner, $organization];
    }
}
