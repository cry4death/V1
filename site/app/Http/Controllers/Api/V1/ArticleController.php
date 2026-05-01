<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ArticleDetailResource;
use App\Http\Resources\Api\ArticleListResource;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = ArticleCategory::query()
            ->withCount(['articles' => function ($q) {
                $q->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
            }])
            ->orderBy('name')
            ->get()
            ->map(fn (ArticleCategory $c) => [
                'id' => $c->id,
                'slug' => $c->slug,
                'name' => $c->name,
                'articles_count' => (int) $c->articles_count,
            ]);

        return response()->json(['data' => $categories]);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Article::query()
            ->published()
            ->with('category')
            ->orderByDesc('published_at');

        if ($categorySlug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('meta_description', 'like', "%{$search}%");
            });
        }

        $limit = (int) $request->query('limit', 0);
        if ($limit > 0 && $limit <= 50) {
            $query->limit($limit);
        }

        return ArticleListResource::collection($query->get());
    }

    public function show(string $slug): ArticleDetailResource
    {
        $article = Article::query()
            ->published()
            ->with(['category', 'authorDoctor'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new ArticleDetailResource($article);
    }
}
