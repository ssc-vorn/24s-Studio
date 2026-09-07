<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'pages.view', 'pages.create', 'pages.update', 'pages.delete', 'pages.publish',
            'services.view', 'services.create', 'services.update', 'services.delete',
            'project_categories.view', 'project_categories.create', 'project_categories.update', 'project_categories.delete',
            'projects.view', 'projects.create', 'projects.update', 'projects.delete', 'projects.publish',
            'blog.view', 'blog.create', 'blog.update', 'blog.delete', 'blog.publish',
            'testimonials.view', 'testimonials.create', 'testimonials.update', 'testimonials.delete',
            'partners.view', 'partners.create', 'partners.update', 'partners.delete',
            'leads.view', 'leads.create', 'leads.update', 'leads.delete',
            'menus.view', 'menus.create', 'menus.update', 'menus.delete',
            'menu_items.view', 'menu_items.create', 'menu_items.update', 'menu_items.delete',
            'themes.view', 'themes.create', 'themes.update', 'themes.delete',
            'settings.view', 'settings.create', 'settings.update', 'settings.delete',
            'seo.view', 'seo.create', 'seo.update', 'seo.delete',
            'media.view', 'media.create', 'media.update', 'media.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
