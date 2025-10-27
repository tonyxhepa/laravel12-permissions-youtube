<?php

namespace App\Livewire\Permissions;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

class PermissionIndex extends Component
{
    use WithPagination;

    public function deletePermission($permissionId)
    {
        Permission::findById($permissionId)->delete();

        $this->dispatch('message', 'Permission deleted successfully.');
    }

    public function render()
    {
        $permissions = \Spatie\Permission\Models\Permission::paginate(10);
        return view('livewire.permissions.permission-index', [
            'permissions' => $permissions,
        ]);
    }
}
