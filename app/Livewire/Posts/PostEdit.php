<?php

namespace App\Livewire\Posts;

use App\Models\Post;
use Livewire\Component;

class PostEdit extends Component
{
    public Post $post;


    public $title;
    public $content;

    public function mount(Post $post)
    {
        $this->post = $post;
        $this->title = $post->title;
        $this->content = $post->content;
    }
    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:500',
        ];
    }

    public function updatePost()
    {
        $this->validate();

        $this->post->update([
            'title' => $this->title,
            'content' => $this->content,
        ]);

        // session()->flash('message', 'Post updated successfully.');

        return redirect()->route('posts.index');
    }
    public function render()
    {
        return view('livewire.posts.post-edit');
    }
}
