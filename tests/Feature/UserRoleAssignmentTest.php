<?php

use App\Livewire\Users\UserCreate;
use App\Livewire\Users\UserEdit;
use App\Livewire\Users\UserIndex;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('User Creation with Roles', function () {
    it('can create a user with roles', function () {
        $role1 = Role::create(['name' => 'role_1']);
        $role2 = Role::create(['name' => 'role_2']);

        Livewire::test(UserCreate::class)
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('password', 'password123')
            ->set('selectedRoles', ['role_1', 'role_2'])
            ->call('createUser')
            ->assertRedirect('/users');

        $newUser = User::where('email', 'john@example.com')->first();
        expect($newUser)->not->toBeNull()
            ->and($newUser->roles)->toHaveCount(2)
            ->and($newUser->roles->pluck('name')->toArray())
            ->toContain('role_1', 'role_2');
    });

    it('can create user without roles', function () {
        Livewire::test(UserCreate::class)
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('password', 'password123')
            ->set('selectedRoles', [])
            ->call('createUser')
            ->assertRedirect('/users');

        $newUser = User::where('email', 'jane@example.com')->first();
        expect($newUser)->not->toBeNull()
            ->and($newUser->roles)->toHaveCount(0);
    });

    it('validates required fields', function () {
        Livewire::test(UserCreate::class)
            ->set('name', '')
            ->set('email', '')
            ->set('password', '')
            ->call('createUser')
            ->assertHasErrors(['name' => 'required'])
            ->assertHasErrors(['email' => 'required'])
            ->assertHasErrors(['password' => 'required']);
    });

    it('validates email format and uniqueness', function () {
        User::factory()->create(['email' => 'existing@example.com']);

        Livewire::test(UserCreate::class)
            ->set('name', 'Test User')
            ->set('email', 'invalid-email')
            ->set('password', 'password123')
            ->call('createUser')
            ->assertHasErrors(['email' => 'email']);

        Livewire::test(UserCreate::class)
            ->set('name', 'Test User')
            ->set('email', 'existing@example.com')
            ->set('password', 'password123')
            ->call('createUser')
            ->assertHasErrors(['email' => 'unique']);
    });

    it('validates password minimum length', function () {
        Livewire::test(UserCreate::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', '123')
            ->call('createUser')
            ->assertHasErrors(['password' => 'min']);
    });

    it('loads all roles except super_admin on mount', function () {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'moderator']);
        Role::create(['name' => 'super_admin']);

        $component = Livewire::test(UserCreate::class);

        expect($component->get('allRoles'))
            ->toContain('admin', 'moderator')
            ->not->toContain('super_admin');
    });

    it('hashes password before storing', function () {
        Livewire::test(UserCreate::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->call('createUser');

        $newUser = User::where('email', 'test@example.com')->first();
        expect($newUser->password)->not->toBe('password123')
            ->and(Hash::check('password123', $newUser->password))->toBeTrue();
    });

    it('redirects to users after successful creation', function () {
        Role::create(['name' => 'role_1']);
        Livewire::test(UserCreate::class)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password123')
            ->set('selectedRoles', ['role_1'])
            ->call('createUser')
            ->assertRedirect('/users');

        // The component should reset, but we can't test this directly
        // as it redirects. The reset happens in the component.
    });
});

describe('User Editing with Roles', function () {
    it('can edit user and update roles', function () {
        $user = User::factory()->create();
        $role1 = Role::create(['name' => 'role_1']);
        $role2 = Role::create(['name' => 'role_2']);
        $role3 = Role::create(['name' => 'role_3']);

        // Initially assign some roles
        $user->assignRole([$role1, $role2]);

        Livewire::test(UserEdit::class, ['user' => $user])
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->set('selectedRoles', ['role_2', 'role_3'])
            ->call('updateUser')
            ->assertRedirect('/users');

        $user->refresh();
        expect($user->name)->toBe('Updated Name')
            ->and($user->email)->toBe('updated@example.com')
            ->and($user->roles)->toHaveCount(2)
            ->and($user->roles->pluck('name')->toArray())
            ->toContain('role_2', 'role_3')
            ->not->toContain('role_1');
    });

    it('can update user without changing roles', function () {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'test_role']);
        $user->assignRole($role);

        Livewire::test(UserEdit::class, ['user' => $user])
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->set('selectedRoles', ['test_role'])
            ->call('updateUser')
            ->assertRedirect('/users');

        $user->refresh();
        expect($user->name)->toBe('Updated Name')
            ->and($user->roles)->toHaveCount(1)
            ->and($user->roles->first()->name)->toBe('test_role');
    });

    it('can remove all roles from user', function () {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'test_role']);
        $user->assignRole($role);

        Livewire::test(UserEdit::class, ['user' => $user])
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->set('selectedRoles', [])
            ->call('updateUser')
            ->assertRedirect('/users');

        $user->refresh();
        expect($user->roles)->toHaveCount(0);
    });

    it('loads user data and roles on mount', function () {
        $user = User::factory()->create(['name' => 'Original Name', 'email' => 'original@example.com']);
        $role = Role::create(['name' => 'test_role']);
        $user->assignRole($role);

        $component = Livewire::test(UserEdit::class, ['user' => $user]);

        expect($component->get('name'))->toBe('Original Name')
            ->and($component->get('email'))->toBe('original@example.com')
            ->and($component->get('selectedRoles'))->toContain('test_role');
    });

    it('validates unique email excluding current user', function () {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        Livewire::test(UserEdit::class, ['user' => $user1])
            ->set('name', 'Updated Name')
            ->set('email', 'user2@example.com')
            ->call('updateUser')
            ->assertHasErrors(['email' => 'unique']);
    });

    it('allows keeping the same email', function () {
        $user = User::factory()->create(['email' => 'test@example.com']);

        Livewire::test(UserEdit::class, ['user' => $user])
            ->set('name', 'Updated Name')
            ->set('email', 'test@example.com')
            ->call('updateUser')
            ->assertRedirect('/users');
    });

    it('can update password when provided', function () {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        Livewire::test(UserEdit::class, ['user' => $user])
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->set('password', 'newpassword123')
            ->call('updateUser')
            ->assertRedirect('/users');

        $user->refresh();
        expect($user->password)->not->toBe($originalPassword)
            ->and(Hash::check('newpassword123', $user->password))->toBeTrue();
    });

    it('keeps original password when not provided', function () {
        $user = User::factory()->create();
        $originalPassword = $user->password;

        Livewire::test(UserEdit::class, ['user' => $user])
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->set('password', '')
            ->call('updateUser')
            ->assertRedirect('/users');

        $user->refresh();
        expect($user->password)->toBe($originalPassword);
    });

    it('validates password minimum length when provided', function () {
        $user = User::factory()->create();

        Livewire::test(UserEdit::class, ['user' => $user])
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->set('password', '123')
            ->call('updateUser')
            ->assertHasErrors(['password' => 'min']);
    });

    it('excludes super_admin role from available roles', function () {
        $user = User::factory()->create();
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'moderator']);
        Role::create(['name' => 'super_admin']);

        $component = Livewire::test(UserEdit::class, ['user' => $user]);

        expect($component->get('allRoles'))
            ->toContain('admin', 'moderator')
            ->not->toContain('super_admin');
    });
});

describe('User Listing and Deletion', function () {
    it('can list users with pagination', function () {
        // Create more than 10 users to test pagination
        User::factory()->count(15)->create();

        Livewire::test(UserIndex::class)
            ->assertViewHas('users', function ($users) {
                return $users->count() === 10; // Default pagination
            });

    });

    it('can delete a user', function () {
        $userToDelete = User::factory()->create();

        Livewire::test(UserIndex::class)
            ->call('deleteUser', $userToDelete->id);

        expect(User::find($userToDelete->id))->toBeNull();
    });

    it('handles deletion of user with roles gracefully', function () {
        $userToDelete = User::factory()->create();
        $role = Role::create(['name' => 'test_role']);
        $userToDelete->assignRole($role);

        Livewire::test(UserIndex::class)
            ->call('deleteUser', $userToDelete->id);

        // User should be deleted
        expect(User::find($userToDelete->id))->toBeNull();

        // Role should still exist
        expect(Role::find($role->id))->not->toBeNull();
    });

    it('handles deletion of user with permissions gracefully', function () {
        $userToDelete = User::factory()->create();
        $permission = Permission::create(['name' => 'test_permission']);
        $userToDelete->givePermissionTo($permission);

        Livewire::test(UserIndex::class)
            ->call('deleteUser', $userToDelete->id);

        // User should be deleted
        expect(User::find($userToDelete->id))->toBeNull();

        // Permission should still exist
        expect(Permission::find($permission->id))->not->toBeNull();
    });
});

describe('User Role Assignment Authorization', function () {
    it('requires authentication to access user management', function () {
        auth()->logout();

        $this->get('/users/create')->assertRedirect('/login');
    });

    it('can be accessed by authenticated users', function () {
        Livewire::test(UserCreate::class)
            ->assertStatus(200);
    });
});

describe('Role-Permission Inheritance', function () {
    it('user inherits permissions from assigned roles', function () {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'test_role']);
        $permission = Permission::create(['name' => 'test_permission']);

        $role->givePermissionTo($permission);
        $user->assignRole($role);

        expect($user->hasPermissionTo('test_permission'))->toBeTrue();
    });

    it('user can have both direct permissions and role permissions', function () {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'test_role']);
        $rolePermission = Permission::create(['name' => 'role_permission']);
        $directPermission = Permission::create(['name' => 'direct_permission']);

        $role->givePermissionTo($rolePermission);
        $user->assignRole($role);
        $user->givePermissionTo($directPermission);

        expect($user->hasPermissionTo('role_permission'))->toBeTrue()
            ->and($user->hasPermissionTo('direct_permission'))->toBeTrue()
            ->and($user->getAllPermissions())->toHaveCount(2);
    });

    it('user permissions are properly synced when roles change', function () {
        $user = User::factory()->create();
        $role1 = Role::create(['name' => 'role_1']);
        $role2 = Role::create(['name' => 'role_2']);
        $permission1 = Permission::create(['name' => 'permission_1']);
        $permission2 = Permission::create(['name' => 'permission_2']);

        $role1->givePermissionTo($permission1);
        $role2->givePermissionTo($permission2);

        // Initially assign role1
        $user->assignRole($role1);
        expect($user->hasPermissionTo('permission_1'))->toBeTrue()
            ->and($user->hasPermissionTo('permission_2'))->toBeFalse();

        // Sync to role2
        $user->syncRoles($role2);
        expect($user->hasPermissionTo('permission_1'))->toBeFalse()
            ->and($user->hasPermissionTo('permission_2'))->toBeTrue();
    });
});
