<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class MediaPolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'media.view'); }
    public function view(User $user, Media $media): bool { return $this->allowed($user, $media->organization_id, 'media.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'media.create'); }
    public function update(User $user, Media $media): bool { return $this->allowed($user, $media->organization_id, 'media.update'); }
    public function delete(User $user, Media $media): bool { return $this->allowed($user, $media->organization_id, 'media.delete'); }
}
