<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PromotionDetailResource;
use App\Http\Resources\Api\PromotionListResource;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PromotionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Promotion::query()
            ->active()
            ->with('category')
            ->orderByDesc('start_date')
            ->orderByDesc('id');

        $limit = (int) $request->query('limit', 10);
        if ($limit <= 0) {
            $limit = 10;
        } elseif ($limit > 30) {
            $limit = 30;
        }

        $query->limit($limit);

        return PromotionListResource::collection($query->get());
    }

    public function show(string $slug): PromotionDetailResource
    {
        $promotion = Promotion::query()
            ->active()
            ->with('category')
            ->where('slug', $slug)
            ->firstOrFail();

        return new PromotionDetailResource($promotion);
    }
}
