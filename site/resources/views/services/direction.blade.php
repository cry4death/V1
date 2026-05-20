@extends('layouts.app')

@section('title', $activeDirection->name . ' — Медицинские услуги — Маяк Здоровья')
@section('meta_description', $activeDirection->description ?? 'Подробная информация об услуге ' . $activeDirection->name . ' в клинике Маяк Здоровья.')
@section('body_class', 'medical-services-page-view services-direction-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-medical-services-page.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/style-promotions-page.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/style-our-doctors-page.css') }}">
@endpush

@section('content')
    <div class="services-page-sea-decor" aria-hidden="true">
        <span class="services-page-sea-item services-page-sea-item--anchor-top"><i class="fa-solid fa-anchor"></i></span>
        <span class="services-page-sea-item services-page-sea-item--ship-upper"><i class="fa-solid fa-ship"></i></span>
        <span class="services-page-sea-item services-page-sea-item--compass-upper"><i class="fa-solid fa-compass"></i></span>
        <span class="services-page-sea-item services-page-sea-item--wave-upper"><i class="fa-solid fa-water"></i></span>
        <span class="services-page-sea-item services-page-sea-item--fish-upper-left"><i class="fa-solid fa-fish"></i></span>
        <span class="services-page-sea-item services-page-sea-item--lighthouse-mid-right"><i class="fa-solid fa-tower-observation"></i></span>
        <span class="services-page-sea-item services-page-sea-item--lifebuoy-mid"><i class="fa-solid fa-life-ring"></i></span>
        <span class="services-page-sea-item services-page-sea-item--ship-lower"><i class="fa-solid fa-ship"></i></span>
    </div>

    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <a href="{{ route('services.index') }}" class="breadcrumb-link">Услуги</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">{{ $activeDirection->name }}</span>
        </nav>
    </div>

    <section class="services-page-section">
        <div class="container services-page-container">

            <div class="services-page-layout">
                <aside class="services-sidebar" aria-label="Категории услуг">
                    <nav class="services-nav">
                        @foreach ($allDirections as $direction)
                            <a href="{{ route('services.direction', $direction->slug) }}"
                               class="services-nav-item @if($direction->id === $activeDirection->id) active @endif"
                               aria-current="{{ $direction->id === $activeDirection->id ? 'page' : 'false' }}">
                                <span class="services-nav-icon"><x-direction-icon :direction="$direction" /></span>
                                <span class="services-nav-label">{{ $direction->name }}</span>
                            </a>
                        @endforeach
                    </nav>
                </aside>

                <main class="services-content">
                    @php
                        $details = is_array($activeDirection->details) ? $activeDirection->details : [];
                        $bannerRel = $details['banner'] ?? $activeDirection->image;
                        $hasRichContent = ! empty($details['general'])
                            || ! empty($details['when_list'])
                            || ! empty($details['treat_list'])
                            || ! empty($details['faq']);
                    @endphp

                    <div class="services-panel" data-category-id="{{ $activeDirection->slug }}">
                        @if ($hasRichContent)
                            <div class="services-category-detail">
                                <div class="category-detail-hero">
                                    <div class="category-detail-hero-left">
                                        <span class="category-detail-sea-mark category-detail-sea-mark--anchor" aria-hidden="true"><i class="fa-solid fa-anchor"></i></span>
                                        <span class="category-detail-sea-mark category-detail-sea-mark--compass" aria-hidden="true"><i class="fa-solid fa-compass"></i></span>
                                        <span class="category-detail-sea-mark category-detail-sea-mark--ship" aria-hidden="true"><i class="fa-solid fa-ship"></i></span>
                                        <span class="category-detail-sea-mark category-detail-sea-mark--wave" aria-hidden="true"><i class="fa-solid fa-water"></i></span>
                                        <div class="category-detail-hero-copy">
                                            <h1 class="category-detail-hero-title">{{ $activeDirection->name }}</h1>
                                            <div class="category-detail-hero-divider"></div>
                                        </div>
                                    </div>
                                    @if (! empty($bannerRel))
                                        <div class="category-detail-hero-right">
                                            <img src="{{ asset($bannerRel) }}" alt="{{ $activeDirection->name }}">
                                        </div>
                                    @endif
                                </div>

                                <div class="category-detail-description">
                                    @if (! empty($details['general']))
                                        <p class="category-detail-general">{{ $details['general'] }}</p>
                                    @endif

                                    @if (! empty($details['when_list']))
                                        <p class="category-detail-subtitle">{{ $details['when_subtitle'] ?? 'Когда обратиться?' }}</p>
                                        <ul class="category-detail-list category-detail-list-icons">
                                            @foreach ($details['when_list'] as $li)
                                                <li><span class="category-detail-list-icon"><i class="fa-solid fa-chevron-right"></i></span><span class="category-detail-list-text">{{ $li }}</span></li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if (! empty($details['conclusion']))
                                        <p class="category-detail-conclusion">{{ $details['conclusion'] }}</p>
                                    @endif

                                    @if (! empty($details['treat_list']))
                                        <p class="category-detail-subtitle">{{ $details['treat_subtitle'] ?? 'Что мы лечим?' }}</p>
                                        <ul class="category-detail-list category-detail-list-icons">
                                            @foreach ($details['treat_list'] as $li)
                                                <li><span class="category-detail-list-icon"><i class="fa-solid fa-chevron-right"></i></span><span class="category-detail-list-text">{{ $li }}</span></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                @if ($activeDirection->services->isNotEmpty())
                                    <div class="category-detail-services">
                                        <h3 class="category-detail-section-title">Услуги по направлению</h3>
                                        <div class="category-services-list">
                                            @foreach ($activeDirection->services as $idx => $service)
                                                <a href="{{ route('services.show', $service->slug) }}" class="category-service-row @if ($idx >= 5) category-service-row-hidden @endif">
                                                    <span class="category-service-name">{{ $service->name }}</span>
                                                    <span class="category-service-right">
                                                        @if ($service->price > 0)
                                                            <span class="category-service-price">От {{ number_format($service->price, 2, '.', ' ') }} BYN</span>
                                                        @endif
                                                        <span class="category-service-chevron"><i class="fa-solid fa-chevron-right"></i></span>
                                                    </span>
                                                </a>
                                            @endforeach
                                        </div>
                                        @if ($activeDirection->services->count() > 5)
                                            <button type="button" class="category-more-btn">Показать ещё</button>
                                        @endif
                                    </div>
                                @endif

                                @if ($directionDoctors->isNotEmpty())
                                    <div class="category-detail-doctors">
                                        <h3 class="category-detail-section-title">Врачи</h3>
                                        <div class="category-doctors-grid">
                                            @foreach ($directionDoctors as $doctor)
                                                @include('partials.doctor-card', ['doctor' => $doctor])
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (! empty($details['faq']) && is_array($details['faq']))
                                    <div class="category-faq-section">
                                        <h3 class="category-faq-title">Часто задаваемые вопросы</h3>
                                        <div class="category-faq-list">
                                            @foreach ($details['faq'] as $faqItem)
                                                @if (empty($faqItem['question']))
                                                    @continue
                                                @endif
                                                <div class="accordion-item">
                                                    <button type="button" class="accordion-header" aria-expanded="false">
                                                        <h3>{{ $faqItem['question'] }}</h3>
                                                        <span class="accordion-icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
                                                    </button>
                                                    <div class="accordion-content">
                                                        <p>{{ $faqItem['answer'] ?? '' }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <h1 class="services-category-title">{{ $activeDirection->name }}</h1>
                            <div class="services-title-divider"></div>
                            @if ($activeDirection->description)
                                <p style="margin-bottom:16px">{{ $activeDirection->description }}</p>
                            @endif
                            <div class="category-services-list">
                                @foreach ($activeDirection->services as $service)
                                    <a href="{{ route('services.show', $service->slug) }}" class="category-service-row">
                                        <span class="category-service-name">{{ $service->name }}</span>
                                        <span class="category-service-right">
                                            @if ($service->price > 0)
                                                <span class="category-service-price">От {{ number_format($service->price, 2, '.', ' ') }} BYN</span>
                                            @endif
                                            <span class="category-service-chevron"><i class="fa-solid fa-chevron-right"></i></span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>

                            @if ($directionDoctors->isNotEmpty())
                                <div class="category-detail-doctors">
                                    <h3 class="category-detail-section-title">Врачи</h3>
                                    <div class="category-doctors-grid">
                                        @foreach ($directionDoctors as $doctor)
                                            @include('partials.doctor-card', ['doctor' => $doctor])
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </main>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('scripts/script-medical-services-page.js') }}"></script>
@endpush
