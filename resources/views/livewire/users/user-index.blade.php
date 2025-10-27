<div class="p-6 w-full">
    <div class="flex justify-between mb-4">
        <div>
            <flux:heading size="xl">User Index</flux:heading>
            <flux:subheading size="lg">List of all users</flux:subheading>
        </div>
        <div>
            @can('create_users')
                <flux:button href="{{ route('users.create') }}" icon:trailing="arrow-up-right">
                    New User
                </flux:button>
            @endcan

        </div>
    </div>
    <flux:separator class="my-4" />


    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">
                        ID
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Name
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Email
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                        <th scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $user->id }}
                        </th>
                        <td class="px-6 py-4">
                            {{ $user->name }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4">
                            @can('update_users')
                                <flux:button href="{{ route('users.edit', $user->id) }}" size="sm" variant="primary"
                                    color="green" icon:trailing="pencil">
                                </flux:button>
                            @endcan
                            @can('delete_users')
                                <flux:button wire:click="deleteUser({{ $user->id }})" size="sm" variant="danger"
                                    wire:confirm="Are you sure you want to delete this user?" color="red"
                                    icon:trailing="trash" class="cursor-pointer">
                                </flux:button>
                            @endcan

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
