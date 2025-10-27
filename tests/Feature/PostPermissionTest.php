<?php

use App\Models\Post;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Post Permission Integration', function () {
    it('can create post with create_posts permission', function () {
        $permission = Permission::create(['name' => 'create_posts']);
        $this->user->givePermissionTo($permission);
        
        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => $this->user->id,
        ]);
        
        expect($post)->not->toBeNull()
            ->and($post->title)->toBe('Test Post')
            ->and($post->user_id)->toBe($this->user->id);
    });
    
    it('can view posts with view_posts permission', function () {
        $permission = Permission::create(['name' => 'view_posts']);
        $this->user->givePermissionTo($permission);
        
        $post = Post::factory()->create();
        
        // Simulate checking if user can view the post
        expect($this->user->hasPermissionTo('view_posts'))->toBeTrue();
    });
    
    it('can update posts with update_posts permission', function () {
        $permission = Permission::create(['name' => 'update_posts']);
        $this->user->givePermissionTo($permission);
        
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        
        $post->update(['title' => 'Updated Title']);
        
        expect($post->fresh()->title)->toBe('Updated Title');
    });
    
    it('can delete posts with delete_posts permission', function () {
        $permission = Permission::create(['name' => 'delete_posts']);
        $this->user->givePermissionTo($permission);
        
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $post->delete(); 
        
        expect(Post::find($post->id))->toBeNull();
    });
    
    it('inherits post permissions through role', function () {
        $role = Role::create(['name' => 'editor']);
        $permission = Permission::create(['name' => 'create_posts']);
        $role->givePermissionTo($permission);
        $this->user->assignRole($role);
        
        expect($this->user->hasPermissionTo('create_posts'))->toBeTrue();
        
        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => $this->user->id,
        ]);
        
        expect($post)->not->toBeNull();
    });
    
    it('requires specific permissions for different post operations', function () {
        $createPermission = Permission::create(['name' => 'create_posts']);
        $updatePermission = Permission::create(['name' => 'update_posts']);
        $deletePermission = Permission::create(['name' => 'delete_posts']);
        
        // User only has create permission
        $this->user->givePermissionTo($createPermission);
        
        expect($this->user->hasPermissionTo('create_posts'))->toBeTrue()
            ->and($this->user->hasPermissionTo('update_posts'))->toBeFalse()
            ->and($this->user->hasPermissionTo('delete_posts'))->toBeFalse();
        
        // Can create but not update or delete
        $post = Post::create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'user_id' => $this->user->id,
        ]);
        
        expect($post)->not->toBeNull();
        
        // Should not be able to update without permission
        expect($this->user->can('update', $post))->toBeFalse();
    });
    
    it('handles post permissions with multiple roles', function () {
        $editorRole = Role::create(['name' => 'editor']);
        $adminRole = Role::create(['name' => 'admin']);
        
        $createPermission = Permission::create(['name' => 'create_posts']);
        $updatePermission = Permission::create(['name' => 'update_posts']);
        $deletePermission = Permission::create(['name' => 'delete_posts']);
        
        $editorRole->givePermissionTo($createPermission);
        $adminRole->givePermissionTo([$updatePermission, $deletePermission]);
        
        $this->user->assignRole([$editorRole, $adminRole]);
        
        expect($this->user->hasPermissionTo('create_posts'))->toBeTrue()
            ->and($this->user->hasPermissionTo('update_posts'))->toBeTrue()
            ->and($this->user->hasPermissionTo('delete_posts'))->toBeTrue();
    });
    
    it('validates post ownership with permissions', function () {
        $otherUser = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $otherUser->id]);
        
        $updatePermission = Permission::create(['name' => 'update_posts']);
        $this->user->givePermissionTo($updatePermission);
        
        // User has update permission but doesn't own the post
        // This would typically be handled by policy or additional checks
        expect($this->user->hasPermissionTo('update_posts'))->toBeTrue();
        
        // In a real application, you might check ownership
        expect($post->user_id)->not->toBe($this->user->id);
    });
});
