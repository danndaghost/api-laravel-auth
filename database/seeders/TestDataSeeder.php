<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos
        $permissions = [
            'view reports',
            'edit articles',
            'manage users',
            'create roles',
            'delete roles',
            'view dashboard',
            'export data',
            'import data'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Crear roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $viewer = Role::firstOrCreate(['name' => 'viewer']);

        // Asignar permisos a roles
        $admin->givePermissionTo(Permission::all());
        $editor->givePermissionTo(['edit articles', 'view reports', 'view dashboard']);
        $viewer->givePermissionTo(['view reports']);

        // Crear usuarios de prueba
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password123')
            ]
        );
        $adminUser->assignRole('admin');

        $editorUser = User::firstOrCreate(
            ['email' => 'editor@test.com'],
            [
                'name' => 'Editor User',
                'password' => bcrypt('password123')
            ]
        );
        $editorUser->assignRole('editor');

        $viewerUser = User::firstOrCreate(
            ['email' => 'viewer@test.com'],
            [
                'name' => 'Viewer User',
                'password' => bcrypt('password123')
            ]
        );
        $viewerUser->assignRole('viewer');
    }
}