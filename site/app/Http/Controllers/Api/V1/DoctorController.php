<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DoctorDetailResource;
use App\Http\Resources\Api\DoctorListResource;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DoctorController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Doctor::query()
            ->active()
            ->with('specialization')
            ->orderBy('sort_order')
            ->orderBy('last_name');

        if ($specSlug = $request->query('specialization')) {
            $query->whereHas('specialization', fn ($q) => $q->where('slug', $specSlug));
        }

        if ($age = $request->query('patient_age')) {
            if (in_array($age, ['adults', 'children', 'both'], true)) {
                $query->where('patient_age', $age);
            }
        }

        return DoctorListResource::collection($query->get());
    }

    public function show(string $slug): DoctorDetailResource
    {
        $doctor = Doctor::query()
            ->active()
            ->with([
                'specialization',
                'services',
                'reviews' => fn ($q) => $q->approved()->latest('published_at'),
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return new DoctorDetailResource($doctor);
    }
}
