<?php

use App\Livewire\Roles\RoleCreate;
use App\Livewire\Roles\RoleEdit;
use App\Livewire\Roles\RoleIndex;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->admin = User::factory()->create();
    $adminRole = Role::create(['name' => 'super_admin']);
    $this->admin->assignRole($adminRole);
    $this->actingAs($this->admin);
});

describe('Role Creation', function () {
    it('can create a role with permissions', function () {
        Permission::create(['name' => 'permission_1']);
        Permission::create(['name' => 'permission_2']);

        Livewire::test(RoleCreate::class)
            ->set('name', 'test_role')
            ->set('selectedPermissions', ['permission_1', 'permission_2'])
            ->call('createRole')
            ->assertRedirect('/roles');

        $role = Role::where('name', 'test_role')->first();
        expect($role)->not->toBeNull()
            ->and($role->permissions)->toHaveCount(2)
            ->and($role->permissions->pluck('name')->toArray())
            ->toContain('permission_1', 'permission_2');
    });

    it('validates required role name', function () {
        Livewire::test(RoleCreate::class)
            ->set('name', '')
            ->call('createRole')
            ->assertHasErrors(['name' => 'required']);
    });

    it('validates unique role name', function () {
        Role::create(['name' => 'existing_role']);

        Livewire::test(RoleCreate::class)
            ->set('name', 'existing_role')
            ->call('createRole')
            ->assertHasErrors(['name' => 'unique']);
    });

    it('validates permission existence', function () {
        Livewire::test(RoleCreate::class)
            ->set('name', 'test_role')
            ->set('selectedPermissions', ['non_existent_permission'])
            ->call('createRole')
            ->assertHasErrors(['selectedPermissions.0' => 'exists']);
    });

    it('can create role without permissions', function () {
        Livewire::test(RoleCreate::class)
            ->set('name', 'test_role')
            ->set('selectedPermissions', [])
            ->call('createRole')
            ->assertRedirect('/roles');

        $role = Role::where('name', 'test_role')->first();
        expect($role)->not->toBeNull()
            ->and($role->permissions)->toHaveCount(0);
    });

    it('loads all permissions on mount', function () {
        Permission::create(['name' => 'permission_1']);
        Permission::create(['name' => 'permission_2']);

        $component = Livewire::test(RoleCreate::class);

        expect($component->get('allPermissions'))
            ->toContain('permission_1', 'permission_2');
    });

    it('redirects after successful creation', function () {
        $permission = Permission::create(['name' => 'permission_1']);
        Livewire::test(RoleCreate::class)
            ->set('name', 'test_role')
            ->set('selectedPermissions', [$permission->name])
            ->call('createRole')
            ->assertRedirect(RoleIndex::class);

    });
});

describe('Role Editing', function () {
    it('can edit a role and update permissions', function () {
        $role = Role::create(['name' => 'original_role']);
        $permission1 = Permission::create(['name' => 'permission_1']);
        $permission2 = Permission::create(['name' => 'permission_2']);
        $permission3 = Permission::create(['name' => 'permission_3']);

        // Initially assign some permissions
        $role->givePermissionTo([$permission1, $permission2]);

        Livewire::test(RoleEdit::class, ['role' => $role])
            ->set('name', 'updated_role')
            ->set('selectedPermissions', ['permission_2', 'permission_3'])
            ->call('updateRole')
            ->assertRedirect('/roles');

        $role->refresh();
        expect($role->name)->toBe('updated_role')
            ->and($role->permissions)->toHaveCount(2)
            ->and($role->permissions->pluck('name')->toArray())
            ->toContain('permission_2', 'permission_3')
            ->not->toContain('permission_1');
    });

    it('loads role data on mount', function () {
        $role = Role::create(['name' => 'test_role']);
        $permission = Permission::create(['name' => 'test_permission']);
        $role->givePermissionTo($permission);

        $component = Livewire::test(RoleEdit::class, ['role' => $role]);

        expect($component->get('name'))->toBe('test_role')
            ->and($component->get('selectedPermissions'))->toContain('test_permission');
    });

    it('validates unique role name excluding current role', function () {
        $role1 = Role::create(['name' => 'role_1']);
        $role2 = Role::create(['name' => 'role_2']);

        Livewire::test(RoleEdit::class, ['role' => $role1])
            ->set('name', 'role_2')
            ->call('updateRole')
            ->assertHasErrors(['name' => 'unique']);
    });

    it('allows keeping the same role name', function () {
        $role = Role::create(['name' => 'test_role']);

        Livewire::test(RoleEdit::class, ['role' => $role])
            ->set('name', 'test_role')
            ->call('updateRole')
            ->assertRedirect('/roles');
    });

    it('validates permission existence during edit', function () {
        $role = Role::create(['name' => 'test_role']);

        Livewire::test(RoleEdit::class, ['role' => $role])
            ->set('name', 'updated_role')
            ->set('selectedPermissions', ['non_existent_permission'])
            ->call('updateRole')
            ->assertHasErrors(['selectedPermissions.0' => 'exists']);
    });
});

describe('Role Listing and Deletion', function () {
    it('can list all roles', function () {
        Role::create(['name' => 'role_1']);
        Role::create(['name' => 'role_2']);

        Livewire::test(RoleIndex::class)
            ->assertViewHas('roles', function ($roles) {
                return $roles->count() === 2;
            });
    });

    it('can delete a role', function () {
        $role = Role::create(['name' => 'test_role']);

        Livewire::test(RoleIndex::class)
            ->call('deleteRole', $role->id)
            ->assertViewHas('roles', function ($roles) use ($role) {
                return $roles->where('id', $role->id)->isEmpty();
            });

        expect(Role::find($role->id))->toBeNull();
    });

    it('handles deletion of role with users gracefully', function () {
        $role = Role::create(['name' => 'test_role']);
        $user = User::factory()->create();
        $user->assignRole($role);

        Livewire::test(RoleIndex::class)
            ->call('deleteRole', $role->id);

        // Role should be deleted and user should have no roles
        expect(Role::find($role->id))->toBeNull();
        $user->refresh();
        expect($user->roles)->toHaveCount(0);
    });
});

describe('Role Management Authorization', function () {
    it('requires authentication to access role management', function () {
        auth()->logout();

        $this->get('/roles')->assertRedirect('/login');
    });

    it('can be accessed by authenticated users', function () {
        Livewire::test(RoleCreate::class)
            ->assertStatus(200);
    });
});
