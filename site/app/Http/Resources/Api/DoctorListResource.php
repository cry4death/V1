<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'last_name' => $this->last_name,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'specialty' => $this->specialization?->name,
            'specialization_slug' => $this->specialization?->slug,
            'category' => $this->category,
            'category_label' => $this->category_label,
            'experience_years' => (int) $this->experience_years,
            'experience_since' => $this->experience_since,
            'experience_summary' => $this->experience_summary,
            'patient_age' => $this->patient_age,
            'rating' => round((float) $this->rating, 2),
            'photo_url' => $this->photo_public_url,
        ];
    }
}
