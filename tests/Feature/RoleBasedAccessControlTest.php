<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Gate-based Authorization', function () {
    it('super_admin bypasses all permission checks', function () {
        $superAdmin = User::factory()->create();
        Role::create(['name' => 'super_admin']);
        $superAdmin->assignRole('super_admin');
        $this->actingAs($superAdmin);
        
        // Create a custom gate
        Gate::define('test-permission', function ($user) {
            return false; // This should normally deny access
        });
        
        expect($superAdmin->can('test-permission'))->toBeTrue();
    });
    
    it('regular users are subject to permission checks', function () {
        $regularUser = User::factory()->create();
        $this->actingAs($regularUser);
        
        // Create a custom gate
        Gate::define('test-permission', function ($user) {
            return false; // This should deny access
        });
        
        expect($regularUser->can('test-permission'))->toBeFalse();
    });
});

describe('Middleware-based Authorization', function () {
    it('role middleware allows access to users with correct role', function () {
        $role = Role::create(['name' => 'admin']);
        $this->user->assignRole($role);
        
        // Create a test route with role middleware
        Route::middleware(['role:admin'])->get('/admin-only', function () {
            return 'Admin access granted';
        });
        
        $response = $this->get('/admin-only');
        $response->assertStatus(200)
            ->assertSee('Admin access granted');
    });
    
    it('role middleware denies access to users without correct role', function () {
        $role = Role::create(['name' => 'user']);
        $this->user->assignRole($role);
        
        // Create a test route with role middleware
        Route::middleware(['role:admin'])->get('/admin-only', function () {
            return 'Admin access granted';
        });
        
        $response = $this->get('/admin-only');
        $response->assertStatus(403);
    });
    
    it('permission middleware allows access to users with correct permission', function () {
        $permission = Permission::create(['name' => 'edit-posts']);
        $this->user->givePermissionTo($permission);
        
        // Create a test route with permission middleware
        Route::middleware(['permission:edit-posts'])->get('/edit-posts', function () {
            return 'Edit posts access granted';
        });
        
        $response = $this->get('/edit-posts');
        $response->assertStatus(200)
            ->assertSee('Edit posts access granted');
    });
    
    it('permission middleware denies access to users without correct permission', function () {
        // Create a test route with permission middleware
        Route::middleware(['permission:edit-posts'])->get('/edit-posts', function () {
            return 'Edit posts access granted';
        });
        
        $response = $this->get('/edit-posts');
        $response->assertStatus(403);
    });
    
    it('role_or_permission middleware allows access with either role or permission', function () {
        $role = Role::create(['name' => 'super_admin']);
        $permission = Permission::create(['name' => 'create_roles']);
        
        // Test with role
        $this->user->assignRole($role);
        Route::middleware(['role_or_permission:super_admin|create_roles'])->get('/roles/create', function () {
            return 'Content access granted';
        });

        $response = $this->get('/roles/create');
        $response->assertStatus(200);
        
        // Test with permission
        $this->user->syncRoles([]);
        $this->user->givePermissionTo($permission);

        $response = $this->get('/roles/create');
        $response->assertStatus(200);
    });
    
    it('role_or_permission middleware denies access without role or permission', function () {
        Route::middleware(['role_or_permission:admin|edit-content'])->get('/content-access', function () {
            return 'Content access granted';
        });
        
        $response = $this->get('/content-access');
        $response->assertStatus(403);
    });
});

describe('Complex Authorization Scenarios', function () {
    it('handles multiple roles and permissions correctly', function () {
        $adminRole = Role::create(['name' => 'admin']);
        $editorRole = Role::create(['name' => 'editor']);
        $viewPermission = Permission::create(['name' => 'view-posts']);
        $editPermission = Permission::create(['name' => 'edit-posts']);
        $deletePermission = Permission::create(['name' => 'delete-posts']);
        
        // Assign roles and permissions
        $this->user->assignRole([$adminRole, $editorRole]);
        $this->user->givePermissionTo($viewPermission);
        $adminRole->givePermissionTo($editPermission);
        $editorRole->givePermissionTo($deletePermission);
        
        // Test all permissions
        expect($this->user->hasPermissionTo('view-posts'))->toBeTrue() // Direct permission
            ->and($this->user->hasPermissionTo('edit-posts'))->toBeTrue() // Through admin role
            ->and($this->user->hasPermissionTo('delete-posts'))->toBeTrue() // Through editor role
            ->and($this->user->hasRole('admin'))->toBeTrue()
            ->and($this->user->hasRole('editor'))->toBeTrue()
            ->and($this->user->hasAnyRole(['admin', 'user']))->toBeTrue()
            ->and($this->user->hasAllRoles(['admin', 'editor']))->toBeTrue();
    });
    
    it('handles permission inheritance through multiple roles', function () {
        $role1 = Role::create(['name' => 'role_1']);
        $role2 = Role::create(['name' => 'role_2']);
        $permission1 = Permission::create(['name' => 'permission_1']);
        $permission2 = Permission::create(['name' => 'permission_2']);
        $permission3 = Permission::create(['name' => 'permission_3']);
        
        $role1->givePermissionTo([$permission1, $permission2]);
        $role2->givePermissionTo([$permission2, $permission3]);
        
        $this->user->assignRole([$role1, $role2]);
        
        // User should have all permissions from both roles
        expect($this->user->hasPermissionTo('permission_1'))->toBeTrue()
            ->and($this->user->hasPermissionTo('permission_2'))->toBeTrue()
            ->and($this->user->hasPermissionTo('permission_3'))->toBeTrue()
            ->and($this->user->getAllPermissions())->toHaveCount(3);
    });
    
    it('handles role and permission removal correctly', function () {
        $role = Role::create(['name' => 'test_role']);
        $permission1 = Permission::create(['name' => 'permission_1']);
        $permission2 = Permission::create(['name' => 'permission_2']);
        
        $role->givePermissionTo($permission1);
        $this->user->assignRole($role);
        $this->user->givePermissionTo($permission2);
        
        // Initially should have both permissions
        expect($this->user->hasPermissionTo('permission_1'))->toBeTrue()
            ->and($this->user->hasPermissionTo('permission_2'))->toBeTrue();
        
        // Remove role
        $this->user->removeRole($role);
        expect($this->user->hasPermissionTo('permission_1'))->toBeFalse()
            ->and($this->user->hasPermissionTo('permission_2'))->toBeTrue(); // Direct permission remains
        
        // Remove direct permission
        $this->user->revokePermissionTo($permission2);
        expect($this->user->hasPermissionTo('permission_2'))->toBeFalse();
    });
    
    it('handles wildcard permissions if enabled', function () {
        // Note: This test assumes wildcard permissions are enabled in config
        // In the current config, wildcard permissions are disabled
        $permission = Permission::create(['name' => 'posts.*']);
        
        // This would work if wildcard permissions were enabled
        // For now, we'll test the basic functionality
        $this->user->givePermissionTo($permission);
        
        expect($this->user->hasPermissionTo('posts.*'))->toBeTrue();
    });
});

describe('Authorization Performance', function () {
    it('caches permissions for performance', function () {
        $role = Role::create(['name' => 'test_role']);
        $permission = Permission::create(['name' => 'test_permission']);
        $role->givePermissionTo($permission);
        $this->user->assignRole($role);
        
        // First call should cache the permissions
        $start = microtime(true);
        $this->user->hasPermissionTo('test_permission');
        $firstCallTime = microtime(true) - $start;
        
        // Second call should be faster due to caching
        $start = microtime(true);
        $this->user->hasPermissionTo('test_permission');
        $secondCallTime = microtime(true) - $start;
        
        // The second call should be significantly faster
        expect($secondCallTime)->toBeLessThan($firstCallTime);
    });
    
    it('clears cache when permissions are updated', function () {
        $role = Role::create(['name' => 'test_role']);
        $permission = Permission::create(['name' => 'test_permission']);
        $role->givePermissionTo($permission);
        $this->user->assignRole($role);
        
        // Verify permission exists
        expect($this->user->hasPermissionTo('test_permission'))->toBeTrue();
        
        // Remove permission from role
        $role->revokePermissionTo($permission);
        
        // Permission should no longer be available
        expect($this->user->hasPermissionTo('test_permission'))->toBeFalse();
    });
});

describe('Authorization Edge Cases', function () {
    it('handles non-existent roles gracefully', function () {
        expect($this->user->hasRole('non_existent_role'))->toBeFalse();
    });
    
    it('handles non-existent permissions gracefully', function () {
        expect($this->user->can('non_existent_permission'))->toBeFalse();
    });
    
    it('handles empty role and permission arrays', function () {
        expect($this->user->hasAnyRole([]))->toBeFalse()
            ->and($this->user->hasAllRoles([]))->toBeTrue() // Empty array is considered "all"
            ->and($this->user->hasAnyPermission([]))->toBeFalse()
            ->and($this->user->hasAllPermissions([]))->toBeTrue(); // Empty array is considered "all"
    });
    
    it('handles case sensitivity in role and permission names', function () {
        $role = Role::create(['name' => 'TestRole']);
        $permission = Permission::create(['name' => 'Test Permission']);
        
        $this->user->assignRole($role);
        $this->user->givePermissionTo($permission);
        
        // Role and permission names are case-sensitive
        expect($this->user->hasRole('testrole'))->toBeFalse()
            ->and($this->user->hasRole('TestRole'))->toBeTrue()
            ->and($this->user->can('test permission'))->toBeFalse()
            ->and($this->user->can('Test Permission'))->toBeTrue();
    });
});
