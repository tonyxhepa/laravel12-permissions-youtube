<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserCreate extends Component
{
    public string $name;
    public string $email;
    public string $password;
    public array $allRoles = [];
    public array $selectedRoles = [];

    public function mount()
    {
        $this->allRoles = Role::whereNot('name', 'super_admin')->pluck('name')->toArray();
    }


    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function createUser()
    {
        $this->validate();

        $newUser = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
        ]);

        if (!empty($this->selectedRoles)) {
            $newUser->assignRole($this->selectedRoles);
        }

        // session()->flash('message', 'User created successfully.');

        $this->reset(['name', 'email', 'password']);

        return redirect()->route('users.index');
    }
    public function render()
    {
        return view('livewire.users.user-create');
    }
}
