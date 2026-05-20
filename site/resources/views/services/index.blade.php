@extends('layouts.app')

@section('title', 'Медицинские услуги — Маяк Здоровья')
@section('meta_description', 'Медицинские услуги нашей клиники - полный спектр медицинской помощи. Диагностика, лечение и профилактика.')
@section('body_class', 'medical-services-page-view services-overview-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-medical-services-page.css') }}">
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
            <span class="breadcrumb-current">Услуги</span>
        </nav>
    </div>

    <section class="page-title services-page-title">
        <div class="container">
            <h1 class="page-title-heading">Медицинские услуги</h1>
            <p class="page-title-text">Полный спектр современных медицинских услуг для всей семьи</p>
        </div>
    </section>

    <section class="services-page-section">
        <div class="container">
            <div class="services-overview-grid">
                @foreach ($directions as $direction)
                    @php $cnt = $direction->services->count(); @endphp
                    <a href="{{ route('services.direction', $direction->slug) }}" class="services-dir-card">
                        <span class="services-dir-card-icon"><x-direction-icon :direction="$direction" /></span>
                        <span class="services-dir-card-name">{{ $direction->name }}</span>
                        @if ($cnt > 0)
                            <span class="services-dir-card-count">
                                {{ $cnt }}&nbsp;{{ $cnt === 1 ? 'услуга' : ($cnt < 5 ? 'услуги' : 'услуг') }}
                            </span>
                        @else
                            <span class="services-dir-card-count"></span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endsection
