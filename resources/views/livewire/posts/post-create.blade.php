<div>
    <flux:heading size="xl">Post Create</flux:heading>
    <flux:subheading size="lg">Create a new post</flux:subheading>
    <flux:separator class="my-4" />
    <section class="w-full md:w-1/2 lg:w-1/3">
        <form wire:submit="createPost" class="flex flex-col gap-6">
            <!-- Title -->
            <flux:input name="title" wire:model="title" :label="__('Title')" type="text" autofocus
                autocomplete="title" :placeholder="__('Post title')" />

            <!-- Content -->
            <flux:textarea name="content" wire:model="content" :label="__('Content')" :placeholder="__('Post content')"
                rows="6" />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full cursor-pointer">
                    Create Post
                </flux:button>
            </div>
        </form>
    </section>
</div>
