<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // post permissions
        Permission::firstOrCreate(['name' => 'view_any_posts']);
        Permission::firstOrCreate(['name' => 'view_posts']);
        Permission::firstOrCreate(['name' => 'create_posts']);
        Permission::firstOrCreate(['name' => 'update_posts']);
        Permission::firstOrCreate(['name' => 'delete_posts']);
        Permission::firstOrCreate(['name' => 'restore_posts']);
        Permission::firstOrCreate(['name' => 'force_delete_posts']);
        Permission::firstOrCreate(['name' => 'force_delete_any_posts']);
        Permission::firstOrCreate(['name' => 'restore_any_posts']);
        Permission::firstOrCreate(['name' => 'replicate_posts']);
        Permission::firstOrCreate(['name' => 'reorder_posts']);
        // user permissions
        Permission::firstOrCreate(['name' => 'view_any_users']);
        Permission::firstOrCreate(['name' => 'view_users']);
        Permission::firstOrCreate(['name' => 'create_users']);
        Permission::firstOrCreate(['name' => 'update_users']);
        Permission::firstOrCreate(['name' => 'delete_users']);
        Permission::firstOrCreate(['name' => 'restore_users']);
        Permission::firstOrCreate(['name' => 'force_delete_users']);
        Permission::firstOrCreate(['name' => 'force_delete_any_users']);
        Permission::firstOrCreate(['name' => 'restore_any_users']);
        Permission::firstOrCreate(['name' => 'replicate_users']);
        Permission::firstOrCreate(['name' => 'reorder_users']);
        // roles permissions
        Permission::firstOrCreate(['name' => 'view_any_roles']);
        Permission::firstOrCreate(['name' => 'view_roles']);
        Permission::firstOrCreate(['name' => 'create_roles']);
        Permission::firstOrCreate(['name' => 'update_roles']);
        Permission::firstOrCreate(['name' => 'delete_roles']);
        Permission::firstOrCreate(['name' => 'restore_roles']);
        Permission::firstOrCreate(['name' => 'force_delete_roles']);
        Permission::firstOrCreate(['name' => 'force_delete_any_roles']);
        Permission::firstOrCreate(['name' => 'restore_any_roles']);
        Permission::firstOrCreate(['name' => 'replicate_roles']);
        Permission::firstOrCreate(['name' => 'reorder_roles']);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles and assign created permissions
        Role::firstOrCreate(['name' => 'moderator'])
            ->givePermissionTo([
                'view_any_users',
                'view_users',
                'create_users',
                'update_users',
                'delete_users',
                'force_delete_users',
                'force_delete_any_users',
                'restore_users',
                'restore_any_users',
                'replicate_users',
                'reorder_users',
                'view_any_posts',
                'view_posts',
                'create_posts',
                'update_posts',
                'delete_posts',
                'force_delete_posts',
                'force_delete_any_posts',
                'restore_posts',
                'restore_any_posts',
                'replicate_posts',
                'reorder_posts',
            ]);

        $adminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminUser = User::firstOrCreate(['name' => 'admin', 'email' => 'admin@admin.com', 'password' => 'password']);
        $adminUser->assignRole($adminRole);
    }
}
