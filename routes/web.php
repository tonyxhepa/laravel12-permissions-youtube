<?php

use App\Livewire\Permissions\PermissionCreate;
use App\Livewire\Permissions\PermissionEdit;
use App\Livewire\Permissions\PermissionIndex;
use App\Livewire\Posts\PostCreate;
use App\Livewire\Posts\PostEdit;
use App\Livewire\Posts\PostIndex;
use App\Livewire\Roles\RoleCreate;
use App\Livewire\Roles\RoleEdit;
use App\Livewire\Roles\RoleIndex;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Users\UserCreate;
use App\Livewire\Users\UserEdit;
use App\Livewire\Users\UserIndex;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::get('users', UserIndex::class)->middleware(
        'can:view_any_users'
    )->name('users.index');
    Route::get('users/create', UserCreate::class)->middleware(
        'can:create_users'
    )->name('users.create');    
    Route::get('users/{user}/edit', UserEdit::class)->middleware(
        'can:update_users'
    )->name('users.edit');

    Route::get('posts', PostIndex::class)->middleware(
        'can:view_any_posts'
    )->name('posts.index');
    Route::get('posts/create', PostCreate::class)->middleware(
        'can:create_posts'
    )->name('posts.create');
    Route::get('posts/{post}/edit', PostEdit::class)->middleware(
        'can:update_posts'
    )->name('posts.edit');

    Route::get('roles', RoleIndex::class)->middleware(
        'can:view_any_roles'
    )->name('roles.index');
    Route::get('roles/create', RoleCreate::class)->middleware(
        'can:create_roles'
    )->name('roles.create');
    Route::get('roles/{role}/edit', RoleEdit::class)->middleware(
        'can:update_roles'
    )->name('roles.edit');

    Route::get('permissions', PermissionIndex::class)->middleware(
        'can:view_any_permissions'
    )->name('permissions.index');
    Route::get('permissions/create', PermissionCreate::class)->middleware(
        'can:create_permissions'
    )->name('permissions.create');
    Route::get('permissions/{permission}/edit', PermissionEdit::class)->middleware(
        'can:update_permissions'
    )->name('permissions.edit');
});
