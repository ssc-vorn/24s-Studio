<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StoreOrganizationMemberRequest;
use App\Http\Requests\CMS\UpdateOrganizationMemberRequest;
use App\Models\Organization;
use App\Models\User;
use App\Support\Tenancy\OrganizationAccess;
use App\Support\Tenancy\OrganizationContext;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(Organization $organization): Response
    {
        $this->assertCanManage($organization);
        $access = app(OrganizationAccess::class);

        return Inertia::render('Admin/CMS/Team/Index', [
            'organization' => ['id' => (string) $organization->getKey(), 'name' => $organization->name],
            'members' => $organization->users()->orderBy('name')->get()->map(fn (User $user) => [
                'id' => (int) $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'role' => $access->role($user, (string) $organization->getKey()) ?? 'legacy',
                'is_owner' => (bool) $user->pivot->is_owner,
            ])->values(),
            'roles' => array_values(array_filter(OrganizationAccess::roles(), fn (string $role) => $role !== 'owner')),
        ]);
    }

    public function store(StoreOrganizationMemberRequest $request, Organization $organization): RedirectResponse
    {
        $this->assertCanManage($organization);
        $user = User::query()->where('email', $request->validated('email'))->firstOrFail();
        $membership = $organization->users()->whereKey($user->getKey())->first();

        if ($membership) {
            abort_if((bool) $membership->pivot->is_owner, 422, 'Transfer ownership before changing the owner role.');
            $organization->users()->updateExistingPivot($user->getKey(), ['role' => $request->validated('role')]);
        } else {
            $organization->users()->attach($user->getKey(), ['is_owner' => false, 'role' => $request->validated('role')]);
        }

        return back()->with('success', 'Member access updated.');
    }

    public function update(UpdateOrganizationMemberRequest $request, Organization $organization, User $user): RedirectResponse
    {
        $this->assertCanManage($organization);
        $membership = $organization->users()->whereKey($user->getKey())->firstOrFail();
        abort_if((bool) $membership->pivot->is_owner, 422, 'Transfer ownership before changing the owner role.');

        $organization->users()->updateExistingPivot($user->getKey(), ['role' => $request->validated('role')]);

        return back()->with('success', 'Member role updated.');
    }

    public function destroy(Organization $organization, User $user): RedirectResponse
    {
        $this->assertCanManage($organization);
        $membership = $organization->users()->whereKey($user->getKey())->firstOrFail();
        abort_if((bool) $membership->pivot->is_owner, 422, 'Transfer ownership before removing the owner.');

        $organization->users()->detach($user->getKey());

        return back()->with('success', 'Member access removed.');
    }

    private function assertCanManage(Organization $organization): void
    {
        $context = app(OrganizationContext::class);
        $context->assertMember((string) $organization->getKey());
        abort_unless($context->can((string) $organization->getKey(), 'members.manage'), 403);
    }
}
