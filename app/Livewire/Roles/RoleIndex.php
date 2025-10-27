<?php

namespace App\Livewire\Roles;

use Livewire\Component;

class RoleIndex extends Component
{
    public function deleteRole($roleId)
    {
        $role = \Spatie\Permission\Models\Role::findById($roleId);
        if ($role) {
            $role->delete();
            session()->flash('message', 'Role deleted successfully.');
        } else {
            session()->flash('error', 'Role not found.');
        }
    }
    public function render()
    {
        $roles = \Spatie\Permission\Models\Role::all();
        return view('livewire.roles.role-index', [
            'roles' => $roles,
        ]);
    }
}
