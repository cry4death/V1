<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Review;
use App\Models\Specialization;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $specializations = Specialization::orderBy('name')->get();

        $query = Doctor::active()->with('specialization')->orderBy('sort_order');

        if ($specSlug = $request->query('spec')) {
            $query->whereHas('specialization', fn ($q) => $q->where('slug', $specSlug));
        }

        if ($age = $request->query('age')) {
            if (in_array($age, ['adults', 'children', 'both'], true)) {
                $query->where('patient_age', $age);
            }
        }

        $doctors = $query->get();

        return view('doctors.index', compact('doctors', 'specializations'));
    }

    public function show(string $slug)
    {
        $doctor = Doctor::active()
            ->with(['specialization', 'services.direction', 'reviews' => fn ($q) => $q->approved()->latest('published_at')])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('doctors.show', compact('doctor'));
    }

    public function storeReview(Request $request, string $slug)
    {
        $doctor = Doctor::active()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'author_name' => ['required', 'string', 'max:100'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'text' => ['required', 'string', 'max:2000'],
        ]);

        Review::create([
            'doctor_id' => $doctor->id,
            'author_name' => $data['author_name'],
            'rating' => $data['rating'],
            'text' => $data['text'],
            'status' => 'pending',
        ]);

        return redirect()->route('doctors.show', $doctor->slug)
            ->with('review_submitted', true);
    }
}
