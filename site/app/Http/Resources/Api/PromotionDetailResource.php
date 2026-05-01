<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items = $this->items;
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'image_url' => $this->imagePublicUrl(),
            'category' => $this->category?->name,
            'category_slug' => $this->category?->slug,
            'start_date' => $this->start_date?->toIso8601String(),
            'end_date' => $this->end_date?->toIso8601String(),
            'items' => array_values(array_map(static fn ($v) => is_string($v) ? $v : (string) $v, $items)),
            'full_description' => $this->fullDescriptionForSite(),
        ];
    }
}
