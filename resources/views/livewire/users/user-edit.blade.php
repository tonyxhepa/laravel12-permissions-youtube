<div>
    <flux:heading size="xl">User Update</flux:heading>
    <flux:subheading size="lg">Update the user</flux:subheading>
    <flux:separator class="my-4" />
    <section class="w-full md:w-1/2 lg:w-1/3">
        <form wire:submit="updateUser" class="flex flex-col gap-6">
            <!-- Name -->
            <flux:input name="name" wire:model="name" :label="__('Name')" type="text" autofocus autocomplete="name"
                :placeholder="__('Full name')" />

            <!-- Email Address -->
            <flux:input name="email" wire:model="email" :label="__('Email address')" type="email"
                autocomplete="email" placeholder="email@example.com" />

            <!-- Password -->
            <flux:input name="password" wire:model="password" :label="__('Password')" type="password"
                autocomplete="new-password" :placeholder="__('Password')" viewable />

            <!-- Roles -->
            <flux:checkbox.group wire:model="selectedRoles" label="Roles" class="flex flex-wrap space-x-4">
                <flux:checkbox.all label="Check all" />
                <flux:separator class="my-2" />
                @foreach ($allRoles as $role)
                    <div class="bg-gray-700 rounded-md px-3 py-1 mb-2">
                        <flux:checkbox label="{{ $role }}" value="{{ $role }}" />
                    </div>
                @endforeach
            </flux:checkbox.group>

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full cursor-pointer">
                    Update User
                </flux:button>
            </div>
        </form>
    </section>
</div>
