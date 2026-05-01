@extends('layouts.app')

@section('title', 'Акции — Маяк Здоровья')
@section('body_class', 'promotions-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-promotions-page.css') }}">
@endpush

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Акции</span>
        </nav>
    </div>

    <section class="promotions-section">
        <div class="promotions-sea-decor" aria-hidden="true">
            <i class="fa-solid fa-anchor psd--anchor-lg"></i>
            <i class="fa-solid fa-tower-observation psd--lighthouse"></i>
            <i class="fa-solid fa-ship psd--ship"></i>
            <i class="fa-solid fa-compass psd--compass"></i>
            <i class="fa-solid fa-anchor psd--anchor-sm"></i>
            <i class="fa-solid fa-water psd--wave"></i>
            <i class="fa-solid fa-fish psd--fish"></i>
            <i class="fa-solid fa-binoculars psd--binoculars"></i>
            <i class="fa-solid fa-life-ring psd--lifebuoy"></i>
            <i class="fa-solid fa-ship psd--ship-low"></i>
            <i class="fa-solid fa-anchor psd--anchor-left"></i>
            <i class="fa-solid fa-water psd--wave-mid"></i>
            <i class="fa-solid fa-compass psd--compass-low"></i>
            <i class="fa-solid fa-dharmachakra psd--wheel-top"></i>
        </div>
        <div class="container">
            <div class="section-header">
                <h2>Акции</h2>
                <p>Выгодные предложения и специальные условия на медицинские услуги. Выберите категорию и узнайте подробности.</p>
            </div>

            <div class="promotions-tabs" role="tablist"></div>

            <div class="promotions-container">
                @forelse ($promotions as $promotion)
                    <a href="{{ route('promotions.show', $promotion->slug) }}" class="promo-card animate-on-scroll" data-category="{{ $promotion->category->name ?? '' }}" style="text-decoration:none;color:inherit">
                        <div class="promo-image">
                            <img src="{{ $promotion->imagePublicUrl('images/blog/heart.jpg') }}" alt="{{ $promotion->title }}">
                        </div>
                        <div class="promo-content">
                            @if ($promotion->category)
                                <span class="promo-badge">{{ $promotion->category->name }}</span>
                            @endif
                            @if ($promotion->start_date || $promotion->end_date)
                                <div class="promo-date-wrap">
                                    <p class="promo-date-range">
                                        Действует
                                        @if ($promotion->start_date) с {{ $promotion->start_date->format('d.m.Y') }} @endif
                                        @if ($promotion->end_date) по {{ $promotion->end_date->format('d.m.Y') }} @endif
                                    </p>
                                </div>
                            @endif
                            <h3 class="promo-title">{{ $promotion->title }}</h3>
                            <p class="promo-description">{{ $promotion->short_description }}</p>
                            <div class="promo-chevron"><div class="promo-chevron-icon"><i class="fa-solid fa-chevron-right"></i></div></div>
                        </div>
                    </a>
                @empty
                    <p>Активных акций пока нет.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('scripts/script-promotions-page.js') }}"></script>
@endpush
