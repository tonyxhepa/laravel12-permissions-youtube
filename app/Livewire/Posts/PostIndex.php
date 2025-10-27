<?php

namespace App\Livewire\Posts;

use Livewire\Component;

class PostIndex extends Component
{
    use \Livewire\WithPagination;
    
    public function deletePost($postId)
    {
        $post = \App\Models\Post::findOrFail($postId);
        $post->delete();
        // session()->flash('message', 'Post deleted successfully.');
    }
    public function render()
    {
        $posts = \App\Models\Post::with('author')->paginate(10);
        return view('livewire.posts.post-index', [
            'posts' => $posts,
        ]);
    }
}
