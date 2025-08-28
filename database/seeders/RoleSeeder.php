<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear permisos base
        Permission::create(['name' => 'view reports']);
        Permission::create(['name' => 'edit articles']);
        Permission::create(['name' => 'manage users']);

        // Crear roles
        $admin = Role::create(['name' => 'admin']);
        $editor = Role::create(['name' => 'editor']);
        $viewer = Role::create(['name' => 'viewer']);

        // Asignar permisos a roles
        $admin->givePermissionTo(Permission::all());
        $editor->givePermissionTo('edit articles');
        $viewer->givePermissionTo('view reports');
    }
}