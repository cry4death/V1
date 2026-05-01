<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'image_url' => $this->imagePublicUrl(),
            'category' => $this->category?->name,
            'category_slug' => $this->category?->slug,
            'end_date' => $this->end_date?->toIso8601String(),
        ];
    }
}
