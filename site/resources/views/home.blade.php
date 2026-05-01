@extends('layouts.app')

@section('title', ($page->content['hero']['title'] ?? 'Маяк Здоровья') . ' — медицинский центр в Минске')
@section('body_class', 'home-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-medical-services-page.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/style-our-doctors-page.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/promotions-slider.css') }}">
@endpush

@php
    $hero = $page->content['hero'] ?? [];
    $features = $page->content['features'] ?? [];
@endphp

@section('content')
    <section class="hero-section">
        <div class="hero-video-bg" aria-hidden="true">
            <video class="hero-video" autoplay muted playsinline preload="metadata">
                <source src="{{ asset('videos/lighthouse.mp4') }}" type="video/mp4">
            </video>
        </div>
        <div class="container">
            <div class="hero-content">
                <p class="hero-label">{{ $hero['subtitle'] ?? 'Медицинский центр в Минске' }}</p>
                <h1>Медицинский центр<br>«{{ $hero['title'] ?? 'Маяк Здоровья' }}»</h1>
                <p class="hero-desc">Амбулаторная помощь, диагностика и лечение по 20 медицинским направлениям. Приём ведут профильные специалисты, доступны лабораторные исследования.</p>
                <div class="hero-buttons">
                    <a href="#appointment" class="btn primary-btn">Записаться на приём <i class="fa-solid fa-arrow-right"></i></a>
                    <a href="#services" class="btn secondary-btn">Наши услуги</a>
                </div>
            </div>
            <div class="hero-image">
                <img src="{{ asset('images/hero-doctor.png') }}" alt="Врач медицинской клиники">
            </div>
        </div>
    </section>

    <section class="features-section home-sea-section">
        <div class="container">
            <div class="features-layout">
                <div class="features-intro">
                    <h2>Забота, основанная на опыте и технологиях</h2>
                    <p>
                        Мы выстраиваем лечение так, чтобы пациент чувствовал себя спокойно на каждом этапе:
                        от первой консультации до полного восстановления.
                    </p>
                    <ul class="features-list">
                        <li><i class="fa-solid fa-check"></i><span>Прозрачные схемы лечения и понятные рекомендации.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Комфортная атмосфера без «больничного» стресса.</span></li>
                        <li><i class="fa-solid fa-check"></i><span>Комплексный подход — диагностика, лечение и профилактика в одном месте.</span></li>
                    </ul>
                </div>
                <div class="features-grid">
                    @forelse ($features as $feature)
                        <div class="category-card animate-on-scroll">
                            <div class="category-icon">
                                <i class="fa-solid {{ $feature['icon'] ?? 'fa-heart' }}"></i>
                            </div>
                            <h3>{{ $feature['title'] ?? '' }}</h3>
                            <p>{{ $feature['text'] ?? '' }}</p>
                        </div>
                    @empty
                        <div class="category-card animate-on-scroll">
                            <div class="category-icon"><i class="fa-solid fa-user-doctor"></i></div>
                            <h3>Опытная команда врачей</h3>
                            <p>Специалисты с высокой квалификацией и многолетней клинической практикой.</p>
                        </div>
                        <div class="category-card animate-on-scroll">
                            <div class="category-icon"><i class="fa-solid fa-microscope"></i></div>
                            <h3>Точная диагностика</h3>
                            <p>Современное оборудование и продуманные диагностические программы.</p>
                        </div>
                        <div class="category-card animate-on-scroll">
                            <div class="category-icon"><i class="fa-solid fa-hand-holding-heart"></i></div>
                            <h3>Комфорт и поддержка</h3>
                            <p>Дружелюбный персонал и внимательное сопровождение на каждом шаге.</p>
                        </div>
                        <div class="category-card animate-on-scroll">
                            <div class="category-icon"><i class="fa-solid fa-hospital-user"></i></div>
                            <h3>Полный цикл помощи</h3>
                            <p>От профилактики и ранней диагностики до лечения и наблюдения.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="service-categories home-service-categories home-sea-section" id="services">
        <div class="container">
            <div class="section-header">
                <h2>Направления</h2>
                <p>Основные специализации клиники — консультации, обследования и лечение</p>
            </div>
            <div class="category-grid">
                @foreach ($directions as $direction)
                    <a href="{{ route('services.index') }}#{{ $direction->slug }}" class="category-card animate-on-scroll">
                        <div class="category-icon"><x-direction-icon :direction="$direction" /></div>
                        <h3>{{ $direction->name }}</h3>
                        <p>{{ Str::limit($direction->description, 110) }}</p>
                        <div class="category-chevron"><div class="category-chevron-icon"><i class="fa-solid fa-chevron-right"></i></div></div>
                    </a>
                @endforeach
            </div>
            <div class="licenses-action">
                <a href="{{ route('services.index') }}" class="btn secondary-btn licenses-btn">Все услуги</a>
            </div>
        </div>
    </section>

    <section id="promotions" class="promotions-slider-section home-sea-section">
        <div class="container">
            <div class="section-header">
                <h2>Акции</h2>
                <p>Выгодные предложения на медицинские услуги. Выберите акцию и узнайте подробности.</p>
            </div>
            <div class="promo-slider-wrapper">
                <button type="button" class="promo-slider-nav promo-slider-prev" aria-label="Предыдущая акция">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="promo-slider-viewport">
                    <div class="promo-slider-track">
                        @if (isset($promoSlides) && $promoSlides->isNotEmpty())
                            @foreach ($promoSlides as $i => $slide)
                                @php $href = $slide->link_url ?: '#'; @endphp
                                <a href="{{ $href }}" class="promo-slide promo-slide--banner" data-index="{{ $i }}">
                                    <span class="promo-slide-inner">
                                        <img src="{{ $slide->imagePublicUrl('images/banner.png') }}" alt="{{ $slide->title ?? 'Акция' }}">
                                    </span>
                                </a>
                            @endforeach
                        @else
                            @foreach ($promotions as $i => $promotion)
                                <a href="{{ route('promotions.show', $promotion->slug) }}" class="promo-slide promo-slide--banner" data-index="{{ $i }}">
                                    <span class="promo-slide-inner">
                                        <img src="{{ $promotion->imagePublicUrl('images/banner.png') }}" alt="{{ $promotion->title }}">
                                    </span>
                                </a>
                            @endforeach
                        @endif
                    </div>
                </div>
                <button type="button" class="promo-slider-nav promo-slider-next" aria-label="Следующая акция">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
            <div class="promo-slider-dots" role="tablist" aria-label="Выбор слайда"></div>
            <div class="licenses-action">
                <a href="{{ route('promotions.index') }}" class="btn secondary-btn licenses-btn">Посмотреть все акции</a>
            </div>
        </div>
    </section>

    <section id="about" class="about-section home-sea-section">
        <div class="container">
            <div class="section-header">
                <h2>О нашей клинике</h2>
                <p>Совмещаем опыт врачей, современную диагностику и бережный сервис для взрослых и детей.</p>
            </div>
            <div class="about-layout">
                <div class="about-visual">
                    <div class="about-image">
                        <img src="{{ asset('images/clinic-building.jpg') }}" alt="Здание клиники «Маяк Здоровья»" loading="lazy">
                    </div>
                    <div class="about-stats">
                        <div class="about-stat animate-on-scroll"><strong>6+</strong><span>лет работы</span></div>
                        <div class="about-stat animate-on-scroll"><strong>{{ $doctors->count() > 0 ? $doctors->count() . '+' : '65' }}</strong><span>врачей в штате</span></div>
                        <div class="about-stat animate-on-scroll"><strong>20</strong><span>направлений</span></div>
                    </div>
                </div>
                <div class="about-text">
                    <p class="about-lead">Клиника «Маяк Здоровья» работает с 2018 года. Начинали с одного кабинета терапевта — сейчас принимаем по 20 направлениям, от кардиологии и неврологии до малоинвазивной хирургии. В штате опытные врачи, среди них кандидаты медицинских наук и специалисты высшей категории.</p>
                    <div class="about-features">
                        <div class="about-feature animate-on-scroll">
                            <div class="category-icon"><i class="fa-solid fa-flask-vial"></i></div>
                            <div>
                                <strong>Своя лаборатория</strong>
                                <span>Результаты анализов за 2–4 часа без ожидания</span>
                            </div>
                        </div>
                        <div class="about-feature animate-on-scroll">
                            <div class="category-icon"><i class="fa-solid fa-laptop-medical"></i></div>
                            <div>
                                <strong>Экспертное оборудование</strong>
                                <span>УЗИ, эндоскопия, функциональная диагностика</span>
                            </div>
                        </div>
                        <div class="about-feature animate-on-scroll">
                            <div class="category-icon"><i class="fa-solid fa-calendar-check"></i></div>
                            <div>
                                <strong>Удобный график</strong>
                                <span>Приём взрослых и детей, включая выходные</span>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('about') }}" class="btn secondary-btn">Подробнее о клинике <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <section id="doctors" class="doctors-section home-doctors home-sea-section">
        <div class="container">
            <div class="section-header">
                <h2>Наши врачи</h2>
                <p>Специалисты с многолетним опытом работы в различных областях медицины</p>
            </div>
            <div class="doctors-slider">
                <button class="slider-nav prev-doctor" aria-label="Предыдущие врачи">&#10094;</button>
                <div class="doctors-slider-viewport">
                    <div class="doctors-container">
                        @foreach ($doctors as $doctor)
                            @include('partials.doctor-card', ['doctor' => $doctor])
                        @endforeach
                    </div>
                </div>
                <button class="slider-nav next-doctor" aria-label="Следующие врачи">&#10095;</button>
            </div>
            <div class="licenses-action">
                <a href="{{ route('doctors.index') }}" class="btn secondary-btn licenses-btn">Посмотреть всех врачей</a>
            </div>
        </div>
    </section>

    <section id="licenses" class="licenses-section home-sea-section">
        <div class="container">
            <div class="section-header">
                <h2>Лицензии и сертификаты</h2>
                <p>Документы, подтверждающие право на оказание медицинских услуг</p>
            </div>
            <div class="licenses-container">
                @foreach ($licenses as $license)
                    <div class="license-card">
                        <div class="license-image">
                            <img src="{{ asset($license->image) }}" alt="{{ $license->caption }}">
                            <div class="license-overlay">
                                <button class="view-license-btn">
                                    <i class="fa-solid fa-magnifying-glass"></i> Увеличить
                                </button>
                            </div>
                        </div>
                        <div class="license-title">{{ $license->caption }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="instagram-section home-sea-section">
        <div class="container">
            <div class="instagram-card">
                <div class="instagram-text">
                    <div class="instagram-label"><i class="fa-brands fa-instagram"></i> Instagram</div>
                    <h2>Давайте дружить в Instagram!</h2>
                    <p>Мы публикуем полезный контент о здоровье, а также информацию о акциях, которые проходят в нашем центре</p>
                    <a href="{{ $social['instagram'] ?? 'https://www.instagram.com/' }}" target="_blank" rel="noopener noreferrer" class="instagram-btn">
                        <i class="fa-brands fa-instagram"></i> Подписаться
                    </a>
                </div>
                <div class="instagram-image">
                    <img src="{{ asset('images/banner-instagram.png') }}" alt="Instagram аккаунт клиники" loading="lazy">
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('scripts/promotions-slider.js') }}"></script>
@endpush
