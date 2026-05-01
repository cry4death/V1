<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ServiceListResource;
use App\Models\Direction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceDirectionController extends Controller
{
    public function index(): JsonResponse
    {
        $directions = Direction::query()
            ->active()
            ->withCount(['services' => function ($q) {
                $q->active();
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn (Direction $d) => [
                'id' => $d->id,
                'slug' => $d->slug,
                'name' => $d->name,
                'services_count' => (int) $d->services_count,
            ]);

        return response()->json(['data' => $directions]);
    }

    public function services(string $slug): AnonymousResourceCollection
    {
        $direction = Direction::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $services = $direction->services()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return ServiceListResource::collection($services);
    }
}
