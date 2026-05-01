<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $categories = ArticleCategory::orderBy('name')->get();

        $articles = Article::published()
            ->with('category')
            ->latest('published_at')
            ->get();

        return view('blog.index', compact('articles', 'categories'));
    }

    public function show(string $slug)
    {
        $article = Article::published()->with('category')->where('slug', $slug)->firstOrFail();

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->when($article->category_id, fn ($q) => $q->where('category_id', $article->category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('blog.show', compact('article', 'related'));
    }
}
