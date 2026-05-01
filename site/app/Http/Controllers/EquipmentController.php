<?php

namespace App\Http\Controllers;

use App\Models\Equipment;

class EquipmentController extends Controller
{
    public function show(string $slug)
    {
        $equipment = Equipment::where('slug', $slug)->firstOrFail();

        return view('equipment.show', compact('equipment'));
    }
}
