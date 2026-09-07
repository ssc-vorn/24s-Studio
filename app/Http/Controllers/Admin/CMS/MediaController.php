<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(Request $request, Organization $organization): Response
    {
        abort_unless($request->user()?->organizations()->whereKey($organization->getKey())->exists(), 403);

        return Inertia::render('Admin/CMS/Media/Index', [
            'organization' => [
                'id' => (string) $organization->getKey(),
                'name' => $organization->name,
            ],
        ]);
    }
}
