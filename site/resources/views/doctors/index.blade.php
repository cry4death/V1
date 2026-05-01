@extends('layouts.app')

@section('title', 'Наши врачи — Маяк Здоровья')
@section('meta_description', 'Наши врачи - квалифицированные специалисты медицинской клиники. Опыт работы, образование, специализация и отзывы пациентов.')
@section('body_class', 'our-doctors-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-our-doctors-page.css') }}">
@endpush

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Наши врачи</span>
        </nav>
    </div>

    <section class="doctors-section" id="doctors">
        <div class="doctors-sea-decor" aria-hidden="true">
            <i class="fa-solid fa-anchor dsd--anchor-lg"></i>
            <i class="fa-solid fa-tower-observation dsd--lighthouse"></i>
            <i class="fa-solid fa-ship dsd--ship"></i>
            <i class="fa-solid fa-compass dsd--compass"></i>
            <i class="fa-solid fa-anchor dsd--anchor-sm"></i>
            <i class="fa-solid fa-water dsd--wave"></i>
            <i class="fa-solid fa-binoculars dsd--binoculars"></i>
            <i class="fa-solid fa-fish dsd--fish"></i>
            <i class="fa-solid fa-life-ring dsd--lifebuoy"></i>
            <i class="fa-solid fa-ship dsd--ship-low"></i>
            <i class="fa-solid fa-anchor dsd--anchor-left"></i>
            <i class="fa-solid fa-water dsd--wave-mid"></i>
            <i class="fa-solid fa-compass dsd--compass-low"></i>
        </div>
        <div class="container">
            <div class="section-header">
                <h2>Наши врачи</h2>
                <p>Квалифицированные специалисты с многолетним опытом работы. Выберите врача по специализации, категории или возрасту пациентов.</p>
            </div>

            <div class="doctors-filters">
                <div class="doctors-search-wrap">
                    <i class="fas fa-search doctors-search-icon" aria-hidden="true"></i>
                    <input type="text" class="doctors-search-input" placeholder="Поиск по имени..." aria-label="Поиск по имени">
                </div>
                <div class="doctors-filters-right">
                    <div class="doctors-filter-group">
                        <div class="custom-select-wrap">
                            <select class="doctors-filter-select" id="filter-specialization" aria-label="Специализация">
                                <option value="">Все специализации</option>
                                @foreach ($specializations as $spec)
                                    <option value="{{ $spec->name }}">{{ $spec->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">Все специализации</button>
                            <div class="custom-select-dropdown" role="listbox" aria-hidden="true"></div>
                        </div>
                    </div>
                    <div class="doctors-filter-group">
                        <div class="custom-select-wrap">
                            <select class="doctors-filter-select" id="filter-category" aria-label="Категория">
                                <option value="">Все категории</option>
                                <option value="Высшая категория">Высшая категория</option>
                                <option value="Первая категория">Первая категория</option>
                                <option value="Вторая категория">Вторая категория</option>
                            </select>
                            <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">Все категории</button>
                            <div class="custom-select-dropdown" role="listbox" aria-hidden="true"></div>
                        </div>
                    </div>
                    <div class="doctors-filter-group">
                        <div class="custom-select-wrap">
                            <select class="doctors-filter-select" id="filter-age" aria-label="Возраст пациентов">
                                <option value="">Все возрасты</option>
                                <option value="adults">Взрослые</option>
                                <option value="children">Дети</option>
                            </select>
                            <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">Все возрасты</button>
                            <div class="custom-select-dropdown" role="listbox" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>
                <button type="button" class="doctors-reset-btn" aria-label="Сбросить фильтры">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>

            <div class="doctors-container">
                @forelse ($doctors as $doctor)
                    @include('partials.doctor-card', ['doctor' => $doctor])
                @empty
                    <p>По вашему запросу врачи не найдены.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('scripts/script-our-doctors-page.js') }}"></script>
@endpush
