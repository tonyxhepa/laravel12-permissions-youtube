<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleCreate extends Component
{
    public string $name;
    public array $allPermissions = [];
    public array $selectedPermissions = [];

    public function mount()
    {
        $this->allPermissions = Permission::pluck('name')->toArray();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|unique:roles,name',
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'string|exists:permissions,name',
        ];
    }
    public function createRole()
    {
        $this->validate();

        $role = Role::create(['name' => $this->name]);
        $role->syncPermissions($this->selectedPermissions);

        // session()->flash('message', 'Role created successfully.');

        // Reset form fields
        $this->reset(['name', 'selectedPermissions']);

        $this->redirect(RoleIndex::class);
    }
    public function render()
    {
        return view('livewire.roles.role-create');
    }
}
