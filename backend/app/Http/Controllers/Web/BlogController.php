<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $featured = BlogPost::where('status', 'Published')->where('featured', true)->orderByDesc('published_at')->first();

        $query = BlogPost::where('status', 'Published')->orderByDesc('published_at');
        if ($featured) {
            $query->where('id', '!=', $featured->id);
        }
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        return view('web.blog.index', [
            'featured' => $featured,
            'posts' => $query->get(),
            'categories' => BlogPost::where('status', 'Published')->distinct()->pluck('category'),
            'activeCategory' => $category ?? null,
        ]);
    }

    public function show(BlogPost $post)
    {
        $related = BlogPost::where('status', 'Published')->where('id', '!=', $post->id)->orderByDesc('published_at')->limit(3)->get();

        return view('web.blog.show', ['post' => $post, 'related' => $related]);
    }
}
