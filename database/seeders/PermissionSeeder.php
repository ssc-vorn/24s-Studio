<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            'pages',
            'services',
            'project_categories',
            'projects',
            'blog_posts',
            'testimonials',
            'partners',
            'leads',
            'menus',
            'menu_items',
            'themes',
            'settings',
            'seo_metadata',
            'media',
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                Permission::findOrCreate("{$resource}.{$action}", 'web');
            }
        }

        Permission::findOrCreate('pages.publish', 'web');
    }
}
