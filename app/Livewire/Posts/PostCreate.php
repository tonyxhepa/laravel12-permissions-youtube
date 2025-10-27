<?php

namespace App\Livewire\Posts;

use App\Models\Post;
use Livewire\Component;

class PostCreate extends Component
{
    public $title;
    public $content;

    protected function rules(): array
    {
         return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:500',
            ];
   }

   public function createPost()
   {
        $this->validate();

        // Logic to create a post (e.g., saving to the database) goes here
        Post::create([
            'title' => $this->title,
            'content' => $this->content,
            'user_id' => auth()->id(),
        ]);
        // Reset form fields after creation
        $this->reset(['title', 'content']);

        // Optionally, you can emit an event or redirect after creation
        // session()->flash('message', 'Post created successfully!');

        return redirect()->route('posts.index');
    }
    public function render()
    {
        return view('livewire.posts.post-create');
    }
}
