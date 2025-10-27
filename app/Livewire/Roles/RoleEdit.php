<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleEdit extends Component
{
    public Role $role;
    public string $name;
    public array $allPermissions = [];
    public array $selectedPermissions = [];

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->allPermissions = Permission::pluck('name')->toArray();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|unique:roles,name,' . $this->role->id,
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'string|exists:permissions,name',
        ];
    }
    public function updateRole()
    {
        $this->validate();

        $this->role->update(['name' => $this->name]);
        $this->role->syncPermissions($this->selectedPermissions);

        // session()->flash('message', 'Role created successfully.');

        // Reset form fields
        $this->name = '';
        $this->selectedPermissions = [];

        return redirect()->route('roles.index');
    }
    public function render()
    {
        return view('livewire.roles.role-edit');
    }
}
