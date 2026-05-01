<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\PromotionCategory;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $categories = PromotionCategory::orderBy('name')->get();

        $promotions = Promotion::active()
            ->with('category')
            ->latest('start_date')
            ->get();

        return view('promotions.index', compact('promotions', 'categories'));
    }

    public function show(string $slug)
    {
        $promotion = Promotion::with('category')->where('slug', $slug)->firstOrFail();

        $related = Promotion::active()
            ->where('id', '!=', $promotion->id)
            ->when($promotion->category_id, fn ($q) => $q->where('category_id', $promotion->category_id))
            ->latest('start_date')
            ->limit(3)
            ->get();

        return view('promotions.show', compact('promotion', 'related'));
    }
}
