<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Media;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(Organization $organization): Response
    {
        $this->authorize('viewAny', [Media::class, (string) $organization->getKey()]);

        return Inertia::render('Admin/CMS/Media/Index', [
            'organization' => [
                'id' => (string) $organization->getKey(),
                'name' => $organization->name,
            ],
        ]);
    }
}
