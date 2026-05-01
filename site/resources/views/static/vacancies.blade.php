@extends('layouts.app')

@section('title', 'Вакансии — Маяк Здоровья')
@section('meta_description', 'Вакансии медицинского центра «Маяк Здоровья»: актуальные позиции для врачей, требования, условия работы и контакты HR-отдела.')
@section('body_class', 'vacancies-page-view')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-vacancies-page.css') }}">
@endpush

@php
    $fallbackVacancies = ['Врач-невролог', 'Врач-уролог', 'Врач-терапевт', 'Врач УЗ-диагностики', 'Врач-кардиолог'];
    $vacanciesList = (isset($vacancies) && $vacancies->count())
        ? $vacancies->map(fn ($v) => $v->title)->values()->all()
        : $fallbackVacancies;
    $hrPhone = '+375296130707';
    $hrPhoneText = '+375-(29)-613-07-07';
@endphp

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Вакансии</span>
        </nav>
    </div>

    <main class="info-page vacancies-page">
        <div class="vacancies-sea-decor" aria-hidden="true">
            <i class="vacancies-sea vacancies-sea--anchor-lg fa-solid fa-anchor"></i>
            <i class="vacancies-sea vacancies-sea--anchor-sm fa-solid fa-anchor"></i>
            <i class="vacancies-sea vacancies-sea--ship fa-solid fa-ship"></i>
            <i class="vacancies-sea vacancies-sea--fish fa-solid fa-fish"></i>
            <i class="vacancies-sea vacancies-sea--wave fa-solid fa-water"></i>
            <i class="vacancies-sea vacancies-sea--compass fa-solid fa-compass"></i>
            <i class="vacancies-sea vacancies-sea--binoculars fa-solid fa-binoculars"></i>
            <i class="vacancies-sea vacancies-sea--lifebuoy fa-solid fa-life-ring"></i>
            <i class="vacancies-sea vacancies-sea--anchor-mid fa-solid fa-anchor"></i>
            <i class="vacancies-sea vacancies-sea--lighthouse fa-solid fa-tower-observation"></i>
        </div>

        <section class="vacancies-intro-section">
            <div class="container">
                <div class="section-header vacancies-page-header">
                    <h1>Вакансии</h1>
                    <p>Мы гарантируем всем нашим сотрудникам достойную оплату и комфортные условия труда. Вы будете работать в сплоченной команде опытных специалистов.</p>
                </div>
            </div>
        </section>

        <section class="vacancies-contact-section">
            <div class="container">
                <article class="vacancies-contact-card vacancies-contact-card--hr animate-on-scroll">
                    <div class="vacancies-contact-icon" aria-hidden="true">
                        <i class="fa-solid fa-phone-volume"></i>
                    </div>
                    <div class="vacancies-contact-body">
                        <h2>Позвоните нам</h2>
                        <p>Чтобы присоединиться к коллективу клиники «Маяк Здоровья» — свяжитесь с нами по номеру телефона <a href="tel:{{ $hrPhone }}" class="vacancies-contact-card__inline-phone">{{ $hrPhoneText }}</a> (Ольга).</p>
                    </div>
                    <a href="tel:{{ $hrPhone }}" class="vacancies-contact-cta">
                        <i class="fa-solid fa-phone"></i>
                        <span>Позвонить</span>
                    </a>
                </article>
            </div>
        </section>

        <section class="career-openings-section">
            <div class="container">
                <div class="section-header">
                    <h2>Присоединяйтесь к команде</h2>
                    <p>Рассматриваем врачей с практическим опытом и готовностью работать в мультидисциплинарной команде.</p>
                </div>

                <div class="career-openings-grid animate-on-scroll">
                    @foreach ($vacanciesList as $i => $vacancy)
                        <article class="career-opening-card">
                            <div class="career-opening-card__meta">
                                <span class="career-opening-card__badge">Вакансия</span>
                                <span class="career-opening-card__index">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <h3>{{ $vacancy }}</h3>
                        </article>
                    @endforeach
                </div>
                <div class="career-openings-note animate-on-scroll">
                    <div class="career-openings-note__icon" aria-hidden="true">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div class="career-openings-note__body">
                        <p class="career-openings-extra-text">Рассматриваем кандидатуры <strong>врачей-специалистов выходного дня</strong> с обсуждением условий оплаты труда и графика работы при собеседовании.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="career-details-section">
            <div class="container">
                <div class="section-header">
                    <h2>Условия и требования</h2>
                    <p>Прозрачные ожидания, понятная нагрузка и поддержка адаптации на старте.</p>
                </div>
                <div class="career-details-grid">
                    <article class="career-detail-card animate-on-scroll">
                        <div class="career-detail-card__head"><h3>Обязанности</h3></div>
                        <ul class="career-detail-list">
                            <li><span class="career-detail-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span class="career-detail-list__text">Квалифицированная медицинская помощь в соответствии с клиническими рекомендациями.</span></li>
                            <li><span class="career-detail-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span class="career-detail-list__text">Корректное ведение медицинской документации и соблюдение стандартов качества.</span></li>
                            <li><span class="career-detail-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span class="career-detail-list__text">Командная работа с коллегами смежных направлений.</span></li>
                        </ul>
                    </article>

                    <article class="career-detail-card animate-on-scroll">
                        <div class="career-detail-card__head"><h3>Требования</h3></div>
                        <ul class="career-detail-list">
                            <li><span class="career-detail-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span class="career-detail-list__text">Высшее медицинское образование и действующий сертификат/категория по специальности.</span></li>
                            <li><span class="career-detail-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span class="career-detail-list__text">Опыт практической работы по специальности от 3 лет.</span></li>
                            <li><span class="career-detail-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span class="career-detail-list__text">Профессиональная этика, аккуратность и ориентированность на пациента.</span></li>
                        </ul>
                    </article>

                    <article class="career-detail-card animate-on-scroll">
                        <div class="career-detail-card__head"><h3>Условия</h3></div>
                        <ul class="career-detail-list">
                            <li><span class="career-detail-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span class="career-detail-list__text">Официальное оформление и социальные гарантии.</span></li>
                            <li><span class="career-detail-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span class="career-detail-list__text">Стабильную оплату труда и современную клиническую базу.</span></li>
                            <li><span class="career-detail-list__icon"><i class="fa-solid fa-chevron-right"></i></span><span class="career-detail-list__text">Возможности роста: внутреннее обучение и профессиональное развитие.</span></li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>
    </main>
@endsection
