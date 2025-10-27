<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    public function deleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->delete();
    }
    public function render(): View
    {
        $users = User::paginate(10);
        return view("livewire.users.user-index", [
            "users" => $users,
        ]);
    }
}
