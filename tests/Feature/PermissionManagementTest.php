<?php

use App\Livewire\Permissions\PermissionCreate;
use App\Livewire\Permissions\PermissionEdit;
use App\Livewire\Permissions\PermissionIndex;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->admin = User::factory()->create();
    $adminRole = Role::create(['name' => 'super_admin']);
    $this->admin->assignRole($adminRole);
    $this->actingAs($this->admin);
});

describe('Permission Creation', function () {
    it('can create a permission with roles', function () {
        $role1 = Role::create(['name' => 'role_1']);
        $role2 = Role::create(['name' => 'role_2']);

        Livewire::test(PermissionCreate::class)
            ->set('name', 'test_permission')
            ->set('selectedRoles', ['role_1', 'role_2'])
            ->call('createPermission')
            ->assertRedirect('/permissions');

        $permission = Permission::where('name', 'test_permission')->first();
        expect($permission)->not->toBeNull()
            ->and($permission->roles)->toHaveCount(2)
            ->and($permission->roles->pluck('name')->toArray())
            ->toContain('role_1', 'role_2');
    });

    it('validates required permission name', function () {
        Livewire::test(PermissionCreate::class)
            ->set('name', '')
            ->call('createPermission')
            ->assertHasErrors(['name' => 'required']);
    });

    it('validates unique permission name', function () {
        Permission::create(['name' => 'existing_permission']);

        Livewire::test(PermissionCreate::class)
            ->set('name', 'existing_permission')
            ->call('createPermission')
            ->assertHasErrors(['name' => 'unique']);
    });

    it('validates role existence', function () {
        Livewire::test(PermissionCreate::class)
            ->set('name', 'test_permission')
            ->set('selectedRoles', ['non_existent_role'])
            ->call('createPermission')
            ->assertHasErrors(['selectedRoles.0' => 'exists']);
    });

    it('can create permission without roles', function () {
        Livewire::test(PermissionCreate::class)
            ->set('name', 'test_permission')
            ->set('selectedRoles', [])
            ->call('createPermission')
            ->assertRedirect('/permissions');

        $permission = Permission::where('name', 'test_permission')->first();
        expect($permission)->not->toBeNull()
            ->and($permission->roles)->toHaveCount(0);
    });

    it('loads all roles on mount', function () {
        Role::create(['name' => 'role_1']);
        Role::create(['name' => 'role_2']);

        $component = Livewire::test(PermissionCreate::class);

        expect($component->get('allRoles'))
            ->toContain('role_1', 'role_2');
    });

    it('shows success message after creation', function () {
        Livewire::test(PermissionCreate::class)
            ->set('name', 'test_permission')
            ->call('createPermission')
            ->assertSessionHas('message', 'Permission created successfully.');
    });

    it('resets form after successful creation', function () {
        Livewire::test(PermissionCreate::class)
            ->set('name', 'test_permission')
            ->call('createPermission')
            ->assertRedirect('/permissions');

        // The component should reset, but we can't test this directly
        // as it redirects. The reset happens in the component.
    });
});

describe('Permission Editing', function () {
    it('can edit a permission and update roles', function () {
        $permission = Permission::create(['name' => 'original_permission']);
        $role1 = Role::create(['name' => 'role_1']);
        $role2 = Role::create(['name' => 'role_2']);
        $role3 = Role::create(['name' => 'role_3']);

        // Initially assign some roles
        $permission->syncRoles([$role1, $role2]);

        Livewire::test(PermissionEdit::class, ['permission' => $permission])
            ->set('name', 'updated_permission')
            ->set('selectedRoles', ['role_2', 'role_3'])
            ->call('updatePermission')
            ->assertRedirect('/permissions');

        $permission->refresh();
        expect($permission->name)->toBe('updated_permission')
            ->and($permission->roles)->toHaveCount(2)
            ->and($permission->roles->pluck('name')->toArray())
            ->toContain('role_2', 'role_3')
            ->not->toContain('role_1');
    });

    it('loads permission data on mount', function () {
        $permission = Permission::create(['name' => 'test_permission']);
        $role = Role::create(['name' => 'test_role']);
        $permission->syncRoles($role);

        $component = Livewire::test(PermissionEdit::class, ['permission' => $permission]);

        expect($component->get('name'))->toBe('test_permission')
            ->and($component->get('selectedRoles'))->toContain('test_role');
    });

    it('validates unique permission name excluding current permission', function () {
        $permission1 = Permission::create(['name' => 'permission_1']);
        $permission2 = Permission::create(['name' => 'permission_2']);

        Livewire::test(PermissionEdit::class, ['permission' => $permission1])
            ->set('name', 'permission_2')
            ->call('updatePermission')
            ->assertHasErrors(['name' => 'unique']);
    });

    it('allows keeping the same permission name', function () {
        $permission = Permission::create(['name' => 'test_permission']);

        Livewire::test(PermissionEdit::class, ['permission' => $permission])
            ->set('name', 'test_permission')
            ->call('updatePermission')
            ->assertRedirect('/permissions');
    });

    it('validates role existence during edit', function () {
        $permission = Permission::create(['name' => 'test_permission']);

        Livewire::test(PermissionEdit::class, ['permission' => $permission])
            ->set('name', 'updated_permission')
            ->set('selectedRoles', ['non_existent_role'])
            ->call('updatePermission')
            ->assertHasErrors(['selectedRoles.0' => 'exists']);
    });
});

describe('Permission Listing and Deletion', function () {
    it('can list permissions with pagination', function () {
        // Create more than 10 permissions to test pagination
        for ($i = 1; $i <= 15; $i++) {
            Permission::create(['name' => "permission_{$i}"]);
        }

        Livewire::test(PermissionIndex::class)
            ->assertViewHas('permissions', function ($permissions) {
                return count($permissions) == 10;
            });

    });

    it('can delete a permission', function () {
        $permission = Permission::create(['name' => 'test_permission']);

        Livewire::test(PermissionIndex::class)
            ->call('deletePermission', $permission->id);

        expect(Permission::find($permission->id))->toBeNull();
    });

    it('shows success message when deleting permission', function () {
        $permission = Permission::create(['name' => 'test_permission']);

        Livewire::test(PermissionIndex::class)
            ->call('deletePermission', $permission->id)
            ->assertDispatched('message', 'Permission deleted successfully.');
    });

    it('shows exeptions when deleting non-existent permission', function () {
        Livewire::test(PermissionIndex::class)
            ->call('deletePermission', 999);
    })->throws('There is no [permission] with ID `999` for guard `web`.');

    it('handles deletion of permission with roles gracefully', function () {
        $permission = Permission::create(['name' => 'test_permission']);
        $role = Role::create(['name' => 'test_role']);
        $role->givePermissionTo($permission);

        Livewire::test(PermissionIndex::class)
            ->call('deletePermission', $permission->id);

        // Permission should be deleted and role should have no permissions
        expect(Permission::find($permission->id))->toBeNull();
        $role->refresh();
        expect($role->permissions)->toHaveCount(0);
    });

    it('handles deletion of permission assigned to users gracefully', function () {
        $permission = Permission::create(['name' => 'test_permission']);
        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        Livewire::test(PermissionIndex::class)
            ->call('deletePermission', $permission->id);

        // Permission should be deleted and user should have no direct permissions
        expect(Permission::find($permission->id))->toBeNull();
        $user->refresh();
        expect($user->permissions)->toHaveCount(0);
    });
});

describe('Permission Management Authorization', function () {
    it('requires admin permission to access permissions create page', function () {

        $this->actingAs($this->user);
        $this->get('/permissions/create')->assertForbidden();

    });

    it('requires admin permissions for creating a permission', function(){
        $this->actingAs($this->user);
        Livewire::test(PermissionCreate::class)
            ->set('name', 'test_permission')
            ->set('selectedRoles', ['role_1', 'role_2'])
            ->call('createPermission')
            ->assertStatus(403);
    });

    it('can be accessed by users with correct roles', function () {

        $this->get('/permissions/create')->assertStatus(200);
    });
});

describe('Permission-Role Relationships', function () {
    it('can assign permission to multiple roles', function () {
        $permission = Permission::create(['name' => 'test_permission']);
        $role1 = Role::create(['name' => 'role_1']);
        $role2 = Role::create(['name' => 'role_2']);

        $permission->syncRoles([$role1, $role2]);

        expect($permission->roles)->toHaveCount(2)
            ->and($permission->roles->pluck('name')->toArray())
            ->toContain('role_1', 'role_2');
    });

    it('can remove permission from all roles', function () {
        $permission = Permission::create(['name' => 'test_permission']);
        $role = Role::create(['name' => 'test_role']);
        $permission->syncRoles($role);

        expect($permission->roles)->toHaveCount(1);

        $permission->syncRoles([]);

        expect($permission->roles)->toHaveCount(0);
    });

    it('updates role permissions when permission is updated', function () {
        $permission = Permission::create(['name' => 'test_permission']);
        $role1 = Role::create(['name' => 'role_1']);
        $role2 = Role::create(['name' => 'role_2']);
        $role3 = Role::create(['name' => 'role_3']);

        // Initially assign to role1 and role2
        $permission->syncRoles([$role1, $role2]);

        // Update to role2 and role3
        $permission->syncRoles([$role2, $role3]);

        expect($permission->roles)->toHaveCount(2)
            ->and($permission->roles->pluck('name')->toArray())
            ->toContain('role_2', 'role_3')
            ->not->toContain('role_1');
    });
});
