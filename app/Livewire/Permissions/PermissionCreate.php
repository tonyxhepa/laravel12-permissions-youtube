<?php

namespace App\Livewire\Permissions;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionCreate extends Component
{

    public string $name;
    public array $selectedRoles = [];
    public array $allRoles = [];

    public function mount()
    {
        $this->allRoles = Role::pluck('name')->toArray();
    }
    public function rules()
    {
        return [
            'name' => 'required|string|unique:permissions,name',
            'selectedRoles' => 'array',
            'selectedRoles.*' => 'string|exists:roles,name',
        ];
    }
    public function createPermission()
    {
        $this->authorize('create', Permission::class);
        $this->validate();

        $permission = Permission::create(['name' => $this->name]);

        $permission->syncRoles($this->selectedRoles);

        session()->flash('message', 'Permission created successfully.');

        // Reset form fields
        $this->name = '';
        $this->selectedRoles = [];

        // return redirect()->route('permissions.index');
        $this->redirect(PermissionIndex::class);
    }
    public function render()
    {
        return view('livewire.permissions.permission-create');
    }
}
