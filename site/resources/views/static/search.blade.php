@extends('layouts.app')

@section('title', 'Результаты поиска — Маяк Здоровья')
@section('meta_description', 'Поиск по сайту медицинской клиники «Маяк Здоровья» — найдите врачей, услуги, статьи и акции.')
@section('body_class', 'search-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-search-page.css') }}?v=6">
@endpush

@php
    $totalCount = $results['doctors']->count() + $results['services']->count() + $results['articles']->count() + $results['promotions']->count();
    $hasQuery = ($query ?? '') !== '';

    $highlight = function ($text, $q) {
        $text = (string) $text;
        if ($text === '') return '';
        $escaped = e($text);
        $q = trim((string) $q);
        if ($q === '') return $escaped;
        $terms = preg_split('/\s+/u', $q, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($terms as $term) {
            $escaped = preg_replace('/(' . preg_quote(e($term), '/') . ')/iu', '<mark>$1</mark>', $escaped);
        }
        return $escaped;
    };
@endphp

@section('content')
    <div class="search-page-sea-decor" aria-hidden="true">
        <i class="fa-solid fa-binoculars spsd-icon spsd-icon--binoculars"></i>
        <i class="fa-solid fa-ship spsd-icon spsd-icon--ship"></i>
        <i class="fa-solid fa-dharmachakra spsd-icon spsd-icon--wheel"></i>
        <i class="fa-solid fa-compass spsd-icon spsd-icon--compass"></i>
        <i class="fa-solid fa-anchor spsd-icon spsd-icon--anchor-xl"></i>
        <i class="fa-solid fa-anchor spsd-icon spsd-icon--anchor-md"></i>
        <i class="fa-solid fa-fish spsd-icon spsd-icon--fish"></i>
        <i class="fa-solid fa-water spsd-icon spsd-icon--wave"></i>
        <i class="fa-solid fa-tower-observation spsd-icon spsd-icon--lighthouse"></i>
        <i class="fa-solid fa-life-ring spsd-icon spsd-icon--lifebuoy"></i>
        <i class="fa-solid fa-ship spsd-icon spsd-icon--ship-low"></i>
        <i class="fa-solid fa-water spsd-icon spsd-icon--wave-mid"></i>
        <i class="fa-solid fa-compass spsd-icon spsd-icon--compass-low"></i>
        <i class="fa-solid fa-anchor spsd-icon spsd-icon--anchor-tilt"></i>
        <i class="fa-solid fa-tower-observation spsd-icon spsd-icon--lighthouse-soft"></i>
        <i class="fa-solid fa-water spsd-icon spsd-icon--wave-wide"></i>
    </div>

    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Поиск</span>
        </nav>
    </div>

    <section class="search-hero-section">
        <div class="search-hero-sea-decor" aria-hidden="true">
            <i class="fa-solid fa-anchor shsd-icon shsd-icon--anchor-lg"></i>
            <i class="fa-solid fa-ship shsd-icon shsd-icon--ship"></i>
            <i class="fa-solid fa-dharmachakra shsd-icon shsd-icon--wheel"></i>
            <i class="fa-solid fa-compass shsd-icon shsd-icon--compass"></i>
            <i class="fa-solid fa-binoculars shsd-icon shsd-icon--binoculars"></i>
            <i class="fa-solid fa-water shsd-icon shsd-icon--wave"></i>
            <i class="fa-solid fa-tower-observation shsd-icon shsd-icon--lighthouse"></i>
            <i class="fa-solid fa-life-ring shsd-icon shsd-icon--lifebuoy"></i>
            <i class="fa-solid fa-fish shsd-icon shsd-icon--fish"></i>
            <i class="fa-solid fa-anchor shsd-icon shsd-icon--anchor-sm"></i>
        </div>
        <div class="container">
            <h1>Результаты поиска</h1>
            <div class="search-form-container">
                <form class="search-form" id="page-search-form" action="{{ route('search') }}" method="GET">
                    <div class="search-input-group">
                        <input type="text" id="search-query" name="q" value="{{ $query }}" placeholder="Введите запрос..." required>
                    </div>
                    <button type="submit" class="search-button">
                        <i class="fa-solid fa-magnifying-glass"></i> Найти
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="search-results-section">
        <div class="container search-results-shell">
            <div class="search-results-sea-decor" aria-hidden="true">
                <i class="fa-solid fa-compass srsd-icon srsd-icon--compass"></i>
                <i class="fa-solid fa-anchor srsd-icon srsd-icon--anchor"></i>
                <i class="fa-solid fa-ship srsd-icon srsd-icon--ship"></i>
                <i class="fa-solid fa-water srsd-icon srsd-icon--wave"></i>
                <i class="fa-solid fa-tower-observation srsd-icon srsd-icon--lighthouse"></i>
                <i class="fa-solid fa-life-ring srsd-icon srsd-icon--lifebuoy"></i>
                <i class="fa-solid fa-binoculars srsd-icon srsd-icon--binoculars"></i>
                <i class="fa-solid fa-dharmachakra srsd-icon srsd-icon--wheel"></i>
                <i class="fa-solid fa-fish srsd-icon srsd-icon--fish"></i>
                <i class="fa-solid fa-anchor srsd-icon srsd-icon--anchor-soft"></i>
            </div>

            <div id="search-info" @if(! $hasQuery) style="display:none" @endif>
                <p>Поиск по запросу: <span id="search-query-display">{{ $hasQuery ? $query : '—' }}</span></p>
                <p>Найдено результатов: <span id="results-count">{{ $totalCount }}</span></p>
            </div>

            <div class="search-categories">
                <button type="button" class="category-tab active" data-category="all">Все результаты@if($hasQuery && $totalCount) <span class="tab-count">{{ $totalCount }}</span>@endif</button>
                <button type="button" class="category-tab" data-category="service">Услуги@if($hasQuery && $results['services']->count()) <span class="tab-count">{{ $results['services']->count() }}</span>@endif</button>
                <button type="button" class="category-tab" data-category="doctor">Врачи@if($hasQuery && $results['doctors']->count()) <span class="tab-count">{{ $results['doctors']->count() }}</span>@endif</button>
                <button type="button" class="category-tab" data-category="article">Блог@if($hasQuery && $results['articles']->count()) <span class="tab-count">{{ $results['articles']->count() }}</span>@endif</button>
                <button type="button" class="category-tab" data-category="promotion">Акции@if($hasQuery && $results['promotions']->count()) <span class="tab-count">{{ $results['promotions']->count() }}</span>@endif</button>
            </div>

            <div id="search-results" class="search-results-container">
                <div class="no-results-message" @if($hasQuery && $totalCount > 0) style="display: none;" @elseif(! $hasQuery) style="display: none;" @endif>
                    <i class="fa-solid fa-magnifying-glass fa-3x"></i>
                    <p>По вашему запросу ничего не найдено. Попробуйте изменить параметры поиска.</p>
                </div>

                <div class="search-placeholder" id="search-placeholder" @if($hasQuery) style="display:none" @endif>
                    <i class="fa-solid fa-magnifying-glass fa-3x"></i>
                    <p>Введите запрос для поиска по сайту</p>
                </div>

                @if ($hasQuery)
                    @foreach ($results['services'] as $service)
                        <div class="result-item service" data-type="service">
                            <div class="result-category service">Услуга</div>
                            <h3 class="result-title">
                                <a href="{{ route('services.show', $service->slug) }}">{!! $highlight($service->name, $query) !!}</a>
                            </h3>
                            @if ($service->description)
                                <p class="result-content">{!! $highlight(\Illuminate\Support\Str::limit(strip_tags($service->description), 180), $query) !!}</p>
                            @endif
                            <div class="result-footer">
                                @if ($service->direction)
                                    <span class="result-badge">{{ $service->direction->name }}</span>
                                @else
                                    <span class="result-badge">Услуга</span>
                                @endif
                                <a href="{{ route('services.show', $service->slug) }}" class="result-link">Подробнее <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($results['doctors'] as $doctor)
                        @php $name = $doctor->full_name ?? trim(($doctor->last_name ?? '').' '.($doctor->first_name ?? '').' '.($doctor->middle_name ?? '')); @endphp
                        <div class="result-item doctor" data-type="doctor">
                            <div class="result-category doctor">Врач</div>
                            <h3 class="result-title">
                                <a href="{{ route('doctors.show', $doctor->slug) }}">{!! $highlight($name, $query) !!}</a>
                            </h3>
                            @if ($doctor->specialization)
                                <p class="result-content">{!! $highlight($doctor->specialization->name, $query) !!}</p>
                            @endif
                            <div class="result-footer">
                                <span class="result-badge">Специалист</span>
                                <a href="{{ route('doctors.show', $doctor->slug) }}" class="result-link">Подробнее <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($results['articles'] as $article)
                        <div class="result-item article" data-type="article">
                            <div class="result-category article">Статья</div>
                            <h3 class="result-title">
                                <a href="{{ route('blog.show', $article->slug) }}">{!! $highlight($article->title, $query) !!}</a>
                            </h3>
                            @if ($article->meta_description)
                                <p class="result-content">{!! $highlight(\Illuminate\Support\Str::limit($article->meta_description, 180), $query) !!}</p>
                            @endif
                            <div class="result-footer">
                                <span class="result-badge">{{ optional($article->category)->name ?? 'Блог' }}</span>
                                @if ($article->published_at)
                                    <span class="result-meta">{{ $article->published_at->format('d.m.Y') }}</span>
                                @endif
                                <a href="{{ route('blog.show', $article->slug) }}" class="result-link">Подробнее <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    @endforeach

                    @foreach ($results['promotions'] as $promo)
                        <div class="result-item promotion" data-type="promotion">
                            <div class="result-category promotion">Акция</div>
                            <h3 class="result-title">
                                <a href="{{ route('promotions.show', $promo->slug) }}">{!! $highlight($promo->title, $query) !!}</a>
                            </h3>
                            @if ($promo->short_description)
                                <p class="result-content">{!! $highlight(\Illuminate\Support\Str::limit($promo->short_description, 180), $query) !!}</p>
                            @endif
                            <div class="result-footer">
                                <span class="result-badge">{{ optional($promo->category)->name ?? 'Акция' }}</span>
                                <a href="{{ route('promotions.show', $promo->slug) }}" class="result-link">Подробнее <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            var tabs = document.querySelectorAll('.search-categories .category-tab');
            if (!tabs.length) return;

            function filter(category) {
                var items = document.querySelectorAll('#search-results .result-item');
                var visible = 0;
                items.forEach(function (item) {
                    if (category === 'all' || item.dataset.type === category) {
                        item.style.display = '';
                        visible++;
                    } else {
                        item.style.display = 'none';
                    }
                });
                var noResults = document.querySelector('.no-results-message');
                if (noResults) {
                    noResults.style.display = (visible === 0 && items.length > 0) ? 'block' : 'none';
                }
                var counter = document.getElementById('results-count');
                if (counter) counter.textContent = visible;
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) { t.classList.remove('active'); });
                    tab.classList.add('active');
                    filter(tab.dataset.category);
                });
            });
        })();
    </script>
@endpush
