<div class="p-6 w-full">
    <div class="flex justify-between mb-4">
        <div>
            <flux:heading size="xl">Posts Index</flux:heading>
            <flux:subheading size="lg">List of all posts</flux:subheading>
        </div>
        <div>
            <flux:button href="{{ route('posts.create') }}" icon:trailing="arrow-up-right">
                New Post
            </flux:button>
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
                        Title
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Author
                    </th>
                    <th scope="col" class="px-6 py-3">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($posts as $post)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                        <th scope="row"
                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            {{ $post->id }}
                        </th>
                        <td class="px-6 py-4">
                            {{ $post->title }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $post->author->name }}
                        </td>
                        <td class="px-6 py-4">
                            <flux:button href="{{ route('posts.edit', $post->id) }}" size="sm" variant="primary"
                                color="green" icon:trailing="pencil">
                            </flux:button>
                            <flux:button wire:click="deletePost({{ $post->id }})" size="sm" variant="danger"
                                wire:confirm="Are you sure you want to delete this user?" color="red"
                                icon:trailing="trash" class="cursor-pointer">
                            </flux:button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
