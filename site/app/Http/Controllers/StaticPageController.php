<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Direction;
use App\Models\Doctor;
use App\Models\Document;
use App\Models\Equipment;
use App\Models\License;
use App\Models\Page;
use App\Models\Promotion;
use App\Models\Service;
use App\Models\Vacancy;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function about()
    {
        $page = Page::where('slug', 'about')->first() ?? new Page(['content' => []]);
        $directions = Direction::orderBy('sort_order')->get();
        $licenses = License::orderBy('sort_order')->get();
        $equipmentItems = Equipment::active()->orderBy('sort_order')->get();

        return view('static.about', compact('page', 'directions', 'licenses', 'equipmentItems'));
    }

    public function documents()
    {
        $documents = Document::active()->orderBy('sort_order')->get();

        return view('static.documents', compact('documents'));
    }

    public function vacancies()
    {
        $vacancies = Vacancy::active()->orderBy('sort_order')->get();

        return view('static.vacancies', compact('vacancies'));
    }

    public function insurance()
    {
        return view('static.insurance');
    }

    public function medicalDevice()
    {
        return view('static.medical-device');
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->input('q', ''));
        $results = [
            'doctors' => collect(),
            'services' => collect(),
            'articles' => collect(),
            'promotions' => collect(),
        ];

        if ($query !== '') {
            $like = '%'.$query.'%';
            $results['doctors'] = Doctor::where('status', 'active')
                ->where(function ($q) use ($like) {
                    $q->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('middle_name', 'like', $like);
                })->limit(10)->get();
            $results['services'] = Service::where('status', 'active')
                ->where('name', 'like', $like)->limit(10)->get();
            $results['articles'] = Article::where('status', 'published')
                ->where('title', 'like', $like)->limit(10)->get();
            $results['promotions'] = Promotion::where('status', 'active')
                ->where('title', 'like', $like)->limit(10)->get();
        }

        return view('static.search', compact('query', 'results'));
    }
}
