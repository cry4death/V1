<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleDetailResource extends JsonResource
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
            'category' => $this->category?->name,
            'category_slug' => $this->category?->slug,
            'author' => $this->author,
            'author_doctor_slug' => $this->authorDoctor?->slug,
            'published_at' => optional($this->published_at)->toIso8601String(),
            'reading_time' => (int) ($this->reading_time ?? 0),
            'meta_description' => $this->meta_description,
            'content' => $this->content,
            'cover_url' => $this->coverPublicUrl(),
        ];
    }
}
