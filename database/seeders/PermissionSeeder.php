<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'media.view',
            'media.create',
            'media.update',
            'media.delete',
        ] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
    }
}
