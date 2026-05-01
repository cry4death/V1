<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use App\Models\Doctor;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        $directions = Direction::active()
            ->with(['services' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        // Группируем врачей по направлениям (через services.direction_id)
        $directionDoctors = collect();
        $doctors = Doctor::active()
            ->with(['specialization', 'services:id,direction_id'])
            ->orderBy('sort_order')
            ->get();

        foreach ($doctors as $doctor) {
            $dirIds = $doctor->services->pluck('direction_id')->unique();
            foreach ($dirIds as $dirId) {
                $directionDoctors[$dirId] = ($directionDoctors[$dirId] ?? collect())->push($doctor);
            }
        }

        foreach ($directionDoctors as $k => $list) {
            $directionDoctors[$k] = $list->unique('id')->values()->take(6);
        }

        return view('services.index', compact('directions', 'directionDoctors'));
    }

    public function show(string $slug)
    {
        $service = Service::active()
            ->with(['direction', 'doctors' => fn ($q) => $q->where('status', 'active')->with('specialization')])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('services.show', compact('service'));
    }
}
