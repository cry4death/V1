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
            ->with(['services' => fn ($q) => $q->active()->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('services.index', compact('directions'));
    }

    public function direction(string $slug)
    {
        $activeDirection = Direction::active()
            ->with(['services' => fn ($q) => $q->active()->orderBy('name')])
            ->where('slug', $slug)
            ->firstOrFail();

        $allDirections = Direction::active()
            ->orderBy('name')
            ->get();

        $directionDoctors = Doctor::active()
            ->with(['specialization', 'services:id,direction_id'])
            ->whereHas('services', fn ($q) => $q->where('direction_id', $activeDirection->id))
            ->orderBy('sort_order')
            ->get()
            ->unique('id')
            ->values()
            ->take(6);

        return view('services.direction', compact('activeDirection', 'allDirections', 'directionDoctors'));
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
