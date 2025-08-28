<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            Log::info("Starting TestDataSeeder");
            
            // Limpiar tablas existentes
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Permission::query()->delete();
            Role::query()->delete();
            User::query()->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            Log::info("Tables cleared successfully");

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
                $perm = Permission::create([
                    'name' => $permission,
                    'guard_name' => 'api'
                ]);
                Log::info("Permission created: {$perm->name}");
            }

            // Crear roles
            $admin = Role::create(['name' => 'admin', 'guard_name' => 'api']);
            $editor = Role::create(['name' => 'editor', 'guard_name' => 'api']);
            $viewer = Role::create(['name' => 'viewer', 'guard_name' => 'api']);
            
            Log::info("Roles created successfully");

            // Asignar permisos a roles
            $admin->givePermissionTo(Permission::all());
            $editor->givePermissionTo(['edit articles', 'view reports', 'view dashboard']);
            $viewer->givePermissionTo(['view reports']);
            
            Log::info("Permissions assigned to roles");

            // Crear usuarios de prueba
            $adminUser = User::create([
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => Hash::make('password123'),
            ]);
            $adminUser->assignRole('admin');

            $editorUser = User::create([
                'name' => 'Editor User',
                'email' => 'editor@test.com',
                'password' => Hash::make('password123'),
            ]);
            $editorUser->assignRole('editor');

            $viewerUser = User::create([
                'name' => 'Viewer User',
                'email' => 'viewer@test.com',
                'password' => Hash::make('password123'),
            ]);
            $viewerUser->assignRole('viewer');
            
            Log::info("Users created and roles assigned");

            DB::commit();
            Log::info("Seeder completed successfully");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error in TestDataSeeder: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
}