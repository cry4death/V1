@extends('layouts.app')

@section('title', $service->name . ' — Маяк Здоровья')
@section('body_class', 'service-detail-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-our-doctors-page.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/style-medical-services-page.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/style-one-medical-service-page.css') }}">
@endpush

@section('content')
    <div class="service-breadcrumb-bar">
        <div class="container">
            <nav class="breadcrumb" aria-label="Хлебные крошки">
                <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
                <span class="breadcrumb-separator">/</span>
                <a href="{{ route('services.index') }}" class="breadcrumb-link">Услуги</a>
                @if ($service->direction)
                    <span class="breadcrumb-separator">/</span>
                    <a href="{{ route('services.index') }}#{{ $service->direction->slug }}" class="breadcrumb-link">{{ $service->direction->name }}</a>
                @endif
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-current">{{ $service->name }}</span>
            </nav>
        </div>
    </div>

    <main class="service-page">
        <div class="service-page-sea-decor" aria-hidden="true">
            <i class="fa-solid fa-tower-observation spsd--lighthouse-top"></i>
            <i class="fa-solid fa-compass spsd--compass-upper-left"></i>
            <i class="fa-solid fa-ship spsd--ship-upper-right"></i>
            <i class="fa-solid fa-anchor spsd--anchor-upper-mid"></i>
            <i class="fa-solid fa-fish spsd--fish-mid-left"></i>
            <i class="fa-solid fa-water spsd--wave-mid-left"></i>
            <i class="fa-solid fa-water spsd--wave-right-mid"></i>
            <i class="fa-solid fa-anchor spsd--anchor-mid-right"></i>
            <i class="fa-solid fa-life-ring spsd--lifebuoy-mid-right"></i>
            <i class="fa-solid fa-compass spsd--compass-lower-left"></i>
            <i class="fa-solid fa-binoculars spsd--binoculars-lower-left"></i>
            <i class="fa-solid fa-dharmachakra spsd--wheel-lower-mid"></i>
            <i class="fa-solid fa-ship spsd--ship-bottom-left"></i>
            <i class="fa-solid fa-fish spsd--fish-bottom-right"></i>
            <i class="fa-solid fa-water spsd--wave-lower-right"></i>
        </div>

        <section class="service-hero-section">
            <div class="service-hero-decor" aria-hidden="true">
                <i class="fa-solid fa-tower-observation shd--lighthouse"></i>
                <i class="fa-solid fa-compass shd--compass-left"></i>
                <i class="fa-solid fa-anchor shd--anchor"></i>
                <i class="fa-solid fa-dharmachakra shd--wheel"></i>
                <i class="fa-solid fa-compass shd--compass"></i>
                <i class="fa-solid fa-fish shd--fish"></i>
                <i class="fa-solid fa-ship shd--ship"></i>
                <i class="fa-solid fa-life-ring shd--lifebuoy"></i>
                <i class="fa-solid fa-binoculars shd--binoculars"></i>
                <i class="fa-solid fa-water shd--wave"></i>
            </div>
            <div class="container">
                <div class="service-hero-content">
                    @if ($service->direction)
                        <span class="service-hero-badge">
                            <i class="fa-solid fa-hand-holding-medical"></i> {{ $service->direction->name }}
                        </span>
                    @endif
                    <h1>{{ $service->name }}</h1>
                    <div class="service-price-block">
                        @if ($service->price > 0)
                            <div class="service-price">
                                <span class="price-label">Стоимость:</span>
                                <span class="price-value">от {{ number_format($service->price, 2, '.', ' ') }} BYN</span>
                            </div>
                            <div class="service-price-divider"></div>
                        @endif
                        <a href="{{ route('booking.start', ['from' => 'service:'.$service->slug]) }}" class="btn primary-btn service-action-btn">
                            <i class="fa-regular fa-calendar-check"></i> Записаться на приём
                        </a>
                    </div>
                </div>
            </div>
        </section>

        @php
            $renderProse = function ($text) {
                $text = trim((string) $text);
                if ($text === '') return '';
                $blocks = preg_split('/\n{2,}/', $text);
                $html = '';
                foreach ($blocks as $block) {
                    $block = trim($block);
                    if ($block === '') continue;
                    $lines = preg_split('/\r?\n/', $block);
                    $lines = array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));
                    $bulletCount = 0;
                    foreach ($lines as $l) {
                        if (preg_match('/^([\-\*•–—]|\d+[\.\)])\s+/u', $l)) $bulletCount++;
                    }
                    $allShort = count($lines) >= 2 && ! array_filter($lines, fn ($l) => mb_strlen($l) > 140);
                    $isList = (count($lines) >= 2 && $bulletCount >= max(2, (int) ceil(count($lines) * 0.6))) || ($allShort && count($lines) >= 3);
                    if ($isList) {
                        $isOrdered = (bool) preg_match('/^\d+[\.\)]\s+/', $lines[0]);
                        $tag = $isOrdered ? 'ol' : 'ul';
                        $html .= '<' . $tag . '>';
                        foreach ($lines as $l) {
                            $clean = preg_replace('/^([\-\*•–—]|\d+[\.\)])\s+/u', '', $l);
                            $html .= '<li>' . e($clean) . '</li>';
                        }
                        $html .= '</' . $tag . '>';
                    } else {
                        $html .= '<p>' . nl2br(e($block)) . '</p>';
                    }
                }
                return $html;
            };
        @endphp

        <section class="service-content-section">
            <div class="container">
                <div class="service-article prose">
                    @if ($service->description)
                        <div class="service-article-block animate-on-scroll">
                            <h2 class="service-article__title">Об услуге</h2>
                            {!! $renderProse($service->description) !!}
                        </div>
                    @endif

                    @if ($service->indications)
                        <div class="service-article-block animate-on-scroll">
                            <h2 class="service-article__title">Показания</h2>
                            {!! $renderProse($service->indications) !!}
                        </div>
                    @endif

                    @if ($service->preparation)
                        <div class="service-article-block animate-on-scroll">
                            <h2 class="service-article__title">Подготовка</h2>
                            {!! $renderProse($service->preparation) !!}
                        </div>
                    @endif
                </div>
            </div>
        </section>

        @if ($service->doctors->isNotEmpty())
            <section class="service-doctors-section">
                <div class="container">
                    <div class="section-header">
                        <h2>Врачи</h2>
                        <p>Врачи, оказывающие данную услугу</p>
                    </div>
                    <div class="service-doctors-container">
                        @foreach ($service->doctors as $doctor)
                            @include('partials.doctor-card', ['doctor' => $doctor])
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>
@endsection
