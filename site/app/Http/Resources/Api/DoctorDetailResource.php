<?php

namespace App\Http\Resources\Api;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $contacts = Setting::getGroup('contacts');
        $schedule = Setting::getGroup('schedule');

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
            'academic_degree' => $this->academic_degree,
            'experience_years' => (int) $this->experience_years,
            'experience_since' => $this->experience_since,
            'experience_summary' => $this->experience_summary,
            'patient_age' => $this->patient_age,
            'rating' => round((float) $this->rating, 2),
            'photo_url' => $this->photo_public_url,
            'description' => $this->description,
            'services' => $this->whenLoaded('services', fn () => $this->services->pluck('name')->values()->all()),
            'education' => $this->education ?? [],
            'reviews' => DoctorReviewResource::collection($this->whenLoaded('reviews')),
            'clinic' => [
                'address' => $contacts['address'] ?? 'г. Минск, ул. К. Туровского, 14',
                'phone' => $contacts['phone_main'] ?? '+375 (17) 215 02 89',
                'schedule' => trim(implode(' ', array_filter([
                    isset($schedule['weekdays']) ? 'Пн–Пт: '.$schedule['weekdays'] : null,
                    isset($schedule['saturday']) ? 'Сб: '.$schedule['saturday'] : null,
                    isset($schedule['sunday']) ? 'Вс: '.$schedule['sunday'] : null,
                ]))) ?: 'Уточняйте в клинике',
            ],
        ];
    }
}
