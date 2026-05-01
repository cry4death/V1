<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Direction;
use App\Models\Doctor;
use App\Models\License;
use App\Models\Page;
use App\Models\PromoSlide;
use App\Models\Promotion;

class HomeController extends Controller
{
    public function index()
    {
        $page = Page::query()->where('slug', 'home')->first();

        if (! $page) {
            $page = new Page([
                'slug' => 'home',
                'title' => 'Главная',
                'content' => [
                    'hero' => [
                        'title' => 'Маяк Здоровья',
                        'subtitle' => 'Медицинский центр в Минске',
                    ],
                    'features' => [],
                ],
            ]);
        }

        $doctors = Doctor::active()->with('specialization')->orderBy('sort_order')->limit(8)->get();
        $directions = Direction::active()->orderBy('sort_order')->limit(5)->get();
        $promotions = Promotion::active()->latest('start_date')->limit(5)->get();
        $promoSlides = PromoSlide::active()->orderBy('sort_order')->get();
        $articles = Article::published()->with('category')->latest('published_at')->limit(3)->get();
        $licenses = License::orderBy('sort_order')->limit(3)->get();

        return view('home', compact('page', 'doctors', 'directions', 'promotions', 'promoSlides', 'articles', 'licenses'));
    }
}
