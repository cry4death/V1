<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'author_name' => $this->author_name,
            'rating' => (int) $this->rating,
            'text' => $this->text,
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
