<div>
    <flux:heading size="xl">Permission Create</flux:heading>
    <flux:subheading size="lg">Create new permission</flux:subheading>
    <flux:separator class="my-4" />
    <section class="w-full">
        <form wire:submit="createPermission" class="flex flex-col gap-6">
            <!-- Name -->
            <flux:input name="name" wire:model="name" :label="__('Name')" type="text" autofocus autocomplete="name"
                :placeholder="__('Permission name')" class="w-md" />

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

            <div class="mt-6">
                <flux:button type="submit" variant="primary" class="w-md cursor-pointer">
                    Create Permission
                </flux:button>
            </div>
        </form>
    </section>
</div>
