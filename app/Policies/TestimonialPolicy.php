<?php

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class TestimonialPolicy
{
    use ChecksOrganizationAccess;

    public function view(User $user, Testimonial $testimonial): bool { return $this->allowed($user, $testimonial->organization_id, 'testimonials.view'); }
    public function create(User $user, string $organizationId): bool { return $this->allowed($user, $organizationId, 'testimonials.create'); }
    public function update(User $user, Testimonial $testimonial): bool { return $this->allowed($user, $testimonial->organization_id, 'testimonials.update'); }
    public function delete(User $user, Testimonial $testimonial): bool { return $this->allowed($user, $testimonial->organization_id, 'testimonials.delete'); }
}
