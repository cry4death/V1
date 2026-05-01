@extends('layouts.app')

@section('title', 'Фракционный лазер «CO2RE» — Маяк Здоровья')
@section('meta_description', 'Фракционный лазер «CO2RE» в клинике «Маяк Здоровья»: описание аппарата, особенности двойной лазерной волны и возможности обновления кожи.')
@section('body_class', 'medical-device-detail-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-medical-device-page.css') }}">
@endpush

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <a href="{{ route('about') }}#equipment" class="breadcrumb-link">О клинике</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Фракционный лазер «CO2RE»</span>
        </nav>
    </div>

    <main class="medical-device-page">
        <div class="device-page-sea-decor" aria-hidden="true">
            <i class="fa-solid fa-tower-observation dpsd--lighthouse-upper-left"></i>
            <i class="fa-solid fa-compass dpsd--compass-upper-right"></i>
            <i class="fa-solid fa-ship dpsd--ship-upper-mid"></i>
            <i class="fa-solid fa-anchor dpsd--anchor-left-mid"></i>
            <i class="fa-solid fa-water dpsd--wave-left-lower"></i>
            <i class="fa-solid fa-fish dpsd--fish-center"></i>
            <i class="fa-solid fa-life-ring dpsd--lifebuoy-right-mid"></i>
            <i class="fa-solid fa-binoculars dpsd--binoculars-lower-left"></i>
            <i class="fa-solid fa-dharmachakra dpsd--wheel-lower-mid"></i>
            <i class="fa-solid fa-ship dpsd--ship-bottom-left"></i>
            <i class="fa-solid fa-compass dpsd--compass-bottom-center"></i>
            <i class="fa-solid fa-anchor dpsd--anchor-lower-right"></i>
            <i class="fa-solid fa-water dpsd--wave-bottom-right"></i>
        </div>

        <section class="device-hero">
            <div class="device-hero-decor" aria-hidden="true">
                <i class="fa-solid fa-compass dhd--compass-left"></i>
                <i class="fa-solid fa-anchor dhd--anchor-top"></i>
                <i class="fa-solid fa-ship dhd--ship-right"></i>
                <i class="fa-solid fa-water dhd--wave-bottom-right"></i>
                <i class="fa-solid fa-tower-observation dhd--lighthouse-right"></i>
            </div>
            <div class="container">
                <div class="device-hero-content">
                    <span class="device-kicker">
                        <i class="fa-solid fa-microscope"></i>
                        Медицинское оборудование
                    </span>
                    <h1 class="device-title">
                        Фракционный лазер «CO2RE» (США) выделяющий волны двойного лазера
                    </h1>
                </div>
            </div>
        </section>

        <section class="device-overview-section">
            <div class="container">
                <div class="device-overview">
                    <div class="device-image-panel animate-on-scroll">
                        <div class="device-image-shell">
                            <img src="{{ asset('images/medical_equipment/frakczionnyj-lazer-1-1.png') }}"
                                 alt="Фракционный лазер «CO2RE»"
                                 loading="eager"
                                 decoding="async">
                        </div>
                    </div>

                    <article class="device-content-panel animate-on-scroll">
                        <h2>Универсальная система для деликатной и глубокой работы</h2>
                        <p>
                            CO2RE – это универсальный лазер инновационных технологий. Он отличается от других фракционных лазеров и является единственным CO2 лазером, имеющим 7 вариантов лечение, 5 из которых предназначены для обновления кожи.
                        </p>
                        <p>
                            Система чрезвычайно гибкая и позволяет осуществлять широкий спектр процедур, в том числе: фракционную обработку внешних, средних и глубоких слоев кожи, традиционное фотоомоложение и удаление дефектов кожи лазером CO2.
                        </p>
                        <p>
                            Особенность лазера CO2RE в том, что это первый лазер CO2, выделяющий волны двойного лазера. Он может целенаправленно обновить как поверхностные, так и глубокие слои кожи: верхний, средний и глубокий или в один момент выполнить обновление всех слоев кожи.
                        </p>
                    </article>
                </div>
            </div>
        </section>
    </main>
@endsection
