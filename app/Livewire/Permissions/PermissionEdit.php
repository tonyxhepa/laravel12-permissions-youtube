<?php

namespace App\Livewire\Permissions;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionEdit extends Component
{
    public Permission $permission;
    public string $name;
    public array $selectedRoles = [];
    public array $allRoles = [];

    public function mount(Permission $permission)
    {
        $this->permission = $permission;
        $this->name = $permission->name;
        $this->selectedRoles = $permission->roles->pluck('name')->toArray();
        $this->allRoles = Role::pluck('name')->toArray();
    }
    public function rules() 
    {
        return [
            'name' => 'required|string|unique:permissions,name,' . $this->permission->id,
            'selectedRoles' => 'array',
            'selectedRoles.*' => 'string|exists:roles,name',
        ];
    }
    public function updatePermission()  
    {
        $this->validate();

        $this->permission->update(['name' => $this->name]);

        $this->permission->syncRoles($this->selectedRoles);

        // session()->flash('message', 'Permission updated successfully.');

        return redirect()->route('permissions.index');
    }
    public function render()
    {
        return view('livewire.permissions.permission-edit');
    }
}
