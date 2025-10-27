<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserEdit extends Component
{
    public User $user;

    public string $name = '';
    public string $email = '';
    public string|null $password = null;
    public array $allRoles = [];
    public array $selectedRoles = [];



    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user)],
            'password' => ['sometimes','nullable', 'string', 'min:8'],
        ];
    }

    public function mount(User $user)
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->allRoles = Role::whereNot('name', 'super_admin')->pluck('name')->toArray();
    }

    public function updateUser()
    {
        $this->validate();

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
            'password' => empty($this->password) ? $this->user->password : bcrypt($this->password),
        ]);

        $this->user->syncRoles($this->selectedRoles);

        // session()->flash('message', 'User created successfully.');

        $this->reset(['name', 'email', 'password']);

        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.users.user-edit');
    }
}
