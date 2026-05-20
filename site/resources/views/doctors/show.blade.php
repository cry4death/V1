@extends('layouts.app')

@section('title', $doctor->full_name . ' — ' . ($doctor->specialization->name ?? 'Врач') . ' | Маяк Здоровья')
@section('meta_description', $doctor->full_name . ' — ' . ($doctor->specialization->name ?? 'Врач') . '. Запись на приём в медицинский центр «Маяк Здоровья».')
@section('body_class', 'doctor-detail-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-doctor-page.css') }}">
@endpush

@php
    $reviews = $doctor->reviews;
    $reviewsCount = $reviews->count();
    $avgRating = $reviewsCount > 0 ? round($reviews->avg('rating'), 1) : (float) $doctor->rating;
    $ratingRounded = (int) round($avgRating);
@endphp

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <a href="{{ route('doctors.index') }}" class="breadcrumb-link">Наши врачи</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">{{ $doctor->last_name }} {{ mb_substr($doctor->first_name, 0, 1) }}.@if ($doctor->middle_name) {{ mb_substr($doctor->middle_name, 0, 1) }}.@endif</span>
        </nav>
    </div>

    @if (session('review_submitted'))
        <div class="container" style="margin-top: 1rem;">
            <div class="review-flash-success" role="status"
                 style="padding: 0.85rem 1.1rem; border-radius: 8px; background: #ecfdf5; border: 1px solid #6ee7b7; color: #065f46;">
                <strong>Спасибо!</strong> Отзыв отправлен и появится на сайте после проверки модератором.
            </div>
        </div>
    @endif

    <main class="doctor-page">
        <section class="doctor-hero" aria-label="Профиль врача">
            <div class="container">
                <div class="doctor-hero-inner">
                    <div class="doctor-hero-info">
                        <span class="doctor-hero-badge">
                            <i class="fa-solid fa-user-doctor"></i> {{ mb_strtoupper($doctor->specialization->name ?? 'клиники') }}
                        </span>
                        <h1 class="doctor-hero-name">{{ $doctor->last_name }}<br>{{ $doctor->first_name }} {{ $doctor->middle_name }}</h1>

                        <div class="doctor-hero-photo-mobile" aria-hidden="true">
                            <img src="{{ asset($doctor->photo ?: 'images/doctor-profile.png') }}" alt="">
                        </div>

                        <nav class="doctor-nav" aria-label="Разделы страницы">
                            <a href="#about" class="doctor-nav-link">О враче</a>
                            @if ($doctor->services->isNotEmpty())
                                <a href="#services" class="doctor-nav-link">Услуги</a>
                            @endif
                            @if (! empty($doctor->education))
                                <a href="#education-experience" class="doctor-nav-link">Опыт и образование</a>
                            @endif
                            <a href="#reviews" class="doctor-nav-link">Отзывы</a>
                        </nav>

                        <ul class="doctor-hero-meta">
                            @if ($doctor->experience_since)
                                <li class="doctor-hero-meta-item">
                                    <i class="fa-regular fa-calendar"></i>
                                    <span>Профессионал с {{ $doctor->experience_since }} года</span>
                                </li>
                            @else
                                <li class="doctor-hero-meta-item">
                                    <i class="fa-regular fa-calendar"></i>
                                    <span>Стаж {{ $doctor->experience_years }} лет</span>
                                </li>
                            @endif
                            @if ($doctor->category_label)
                                <li class="doctor-hero-meta-item">
                                    <i class="fa-solid fa-medal"></i>
                                    <span>{{ $doctor->category_label }}</span>
                                </li>
                            @endif
                            @if (filled($doctor->academic_degree))
                                <li class="doctor-hero-meta-item">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                    <span>{{ $doctor->academic_degree }}</span>
                                </li>
                            @endif
                            <li class="doctor-hero-meta-item">
                                <i class="fa-solid fa-location-dot"></i>
                                <a href="{{ route('contacts') }}" class="doctor-hero-meta-link">{{ $contacts['address'] ?? 'г. Минск, ул. К. Туровского, 14' }}</a>
                            </li>
                            <li class="doctor-hero-meta-item">
                                <i class="fa-solid fa-anchor"></i>
                                <span>Рейтинг: {{ number_format($avgRating, 2, '.', '') }}</span>
                            </li>
                            @if (! empty($contacts['phone_main']))
                                <li class="doctor-hero-meta-item">
                                    <i class="fa-solid fa-phone"></i>
                                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $contacts['phone_main']) }}" class="doctor-hero-meta-link">{{ $contacts['phone_main'] }}</a>
                                </li>
                            @endif
                        </ul>

                        <div class="doctor-hero-actions">
                            <a href="{{ route('booking.start', ['from' => 'doctor:'.$doctor->slug]) }}" class="btn doctor-hero-cta">Записаться</a>
                            <button type="button" class="btn secondary-btn" id="hero-open-review-btn">Оставить отзыв</button>
                        </div>
                    </div>

                    <div class="doctor-hero-photo">
                        <img src="{{ asset($doctor->photo ?: 'images/doctor-profile.png') }}" alt="{{ $doctor->full_name }}" width="400" height="460">
                    </div>
                </div>
            </div>
        </section>

        <div class="doctor-content-wrap">
            <div class="container">
                <section id="about" class="doctor-section">
                    <h2 class="doctor-section-title">О враче</h2>
                    <p class="doctor-intro">{{ $doctor->description }}</p>
                </section>

                @if ($doctor->services->isNotEmpty())
                    <section id="services" class="doctor-section">
                        <h2 class="doctor-section-title">Услуги</h2>
                        <ul class="doctor-services-list">
                            @foreach ($doctor->services as $service)
                                <li class="doctor-service-row">
                                    <i class="fa-solid fa-chevron-right"></i>
                                    <a href="{{ route('services.show', $service->slug) }}">{{ $service->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if (! empty($doctor->education))
                    <section id="education-experience" class="doctor-section">
                        <h2 class="doctor-section-title">Образование и опыт работы</h2>
                        <h3 class="doctor-subsection-title">Стаж работы</h3>
                        <ul class="doctor-timeline">
                            @foreach ($doctor->education as $item)
                                @if (($item['type'] ?? 'experience') === 'experience')
                                    @php $period = trim($item['period'] ?? ($item['year'] ?? '')); $title = trim($item['title'] ?? ''); $inst = trim($item['institution'] ?? ''); @endphp
                                    <li @if ($period === '') class="doctor-timeline-no-date" @endif>
                                        @if ($period !== '')
                                            <strong>{{ $period }}</strong>
                                        @endif
                                        @if ($title !== ''){{ $title }}@endif@if ($title !== '' && $inst !== ''), @endif@if ($inst !== ''){{ $inst }}@endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                        <h3 class="doctor-subsection-title">Образование и повышения квалификации</h3>
                        <ul class="doctor-timeline">
                            @foreach ($doctor->education as $item)
                                @if (($item['type'] ?? 'experience') === 'education')
                                    @php $period = trim($item['period'] ?? ($item['year'] ?? '')); $title = trim($item['title'] ?? ''); $inst = trim($item['institution'] ?? ''); @endphp
                                    <li @if ($period === '') class="doctor-timeline-no-date" @endif>
                                        @if ($period !== '')
                                            <strong>{{ $period }}</strong>
                                        @endif
                                        @if ($title !== ''){{ $title }}@endif@if ($title !== '' && $inst !== ''), @endif@if ($inst !== ''){{ $inst }}@endif
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section id="reviews" class="doctor-section">
                    <h2 class="doctor-section-title">Отзывы о враче</h2>

                    <div class="reviews-header">
                        <div class="reviews-summary">
                            <div class="reviews-summary-score">
                                <span class="reviews-summary-number">{{ number_format($avgRating, 1, '.', '') }}</span>
                                <div class="reviews-summary-stars" aria-label="{{ $avgRating }} из 5">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fa-solid fa-anchor @if ($i >= $ratingRounded) anchor-empty @endif"></i>
                                    @endfor
                                </div>
                                <span class="reviews-summary-count">{{ $reviewsCount }} {{ \App\Support\RussianPlural::afterNumber($reviewsCount, 'отзыв', 'отзыва', 'отзывов') }}</span>
                            </div>
                        </div>
                    </div>

                    @if ($reviewsCount > 0)
                        <div class="reviews-slider-section">
                            <button class="reviews-slider-btn reviews-slider-prev" type="button" aria-label="Предыдущие отзывы">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <div class="reviews-slider-viewport">
                                <div class="reviews-slider-track" id="reviews-track">
                                    @foreach ($reviews as $review)
                                        <div class="review-card">
                                            <div class="review-card-header">
                                                <div class="review-card-avatar">{{ mb_substr($review->author_name, 0, 1) }}</div>
                                                <div class="review-card-author-info">
                                                    <span class="review-card-name">{{ $review->author_name }}</span>
                                                    @if ($review->published_at)
                                                        <span class="review-card-date">{{ $review->published_at->translatedFormat('d F Y') }}</span>
                                                    @endif
                                                </div>
                                                <div class="review-card-stars" aria-label="Оценка: {{ $review->rating }} из 5">
                                                    @for ($i = 0; $i < $review->rating; $i++)<i class="fa-solid fa-anchor"></i>@endfor
                                                </div>
                                            </div>
                                            <p class="review-card-text">{{ $review->text }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <button class="reviews-slider-btn reviews-slider-next" type="button" aria-label="Следующие отзывы">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="reviews-dots" id="reviews-dots" aria-label="Переключение страниц отзывов"></div>
                    @else
                        <p>Пока нет отзывов. Станьте первым!</p>
                    @endif

                    <div class="reviews-action">
                        <button type="button" class="btn secondary-btn reviews-open-form-btn" id="reviews-open-form-btn">
                            <i class="fa-solid fa-pen-to-square"></i> Оставить отзыв
                        </button>
                    </div>
                </section>
            </div>
        </div>
    </main>

    {{-- Модалка формы отзыва --}}
    <div class="review-form-modal-overlay" id="review-form-modal-overlay" hidden>
        <div class="review-form-modal" role="dialog" aria-modal="true" aria-labelledby="review-form-modal-title">
            <button class="review-modal-close" id="review-form-modal-close" aria-label="Закрыть">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="review-form-wrap" id="review-form-wrap">
                <form class="review-form" id="review-form" method="POST" action="{{ route('reviews.store', $doctor->slug) }}" novalidate>
                    @csrf
                    <h3 class="review-form-title" id="review-form-modal-title">Ваш отзыв</h3>

                    <div class="review-form-group">
                        <label class="review-form-label" for="review-name">Ваше имя <span class="review-form-required">*</span></label>
                        <input class="review-form-input" type="text" id="review-name" name="author_name" placeholder="Ваше имя" autocomplete="name" required>
                        <span class="review-form-error" id="review-name-error" hidden>Пожалуйста, укажите ваше имя</span>
                    </div>

                    <div class="review-form-group">
                        <label class="review-form-label">Оценка <span class="review-form-required">*</span></label>
                        <div class="review-stars-input" id="review-stars-input" role="radiogroup" aria-label="Выберите оценку">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" class="review-star-btn" data-value="{{ $i }}" aria-label="{{ $i }} якорь"><i class="fa-solid fa-anchor"></i></button>
                            @endfor
                        </div>
                        <input type="hidden" name="rating" id="review-rating-input" value="5">
                        <span class="review-form-error" id="review-stars-error" hidden>Пожалуйста, поставьте оценку</span>
                    </div>

                    <div class="review-form-group">
                        <label class="review-form-label" for="review-text">Ваш отзыв <span class="review-form-required">*</span></label>
                        <textarea class="review-form-textarea" id="review-text" name="text" rows="4" placeholder="Поделитесь впечатлениями о приёме..." required></textarea>
                        <span class="review-form-error" id="review-text-error" hidden>Пожалуйста, напишите отзыв</span>
                    </div>

                    <div class="review-form-actions">
                        <button type="button" class="btn secondary-btn" id="reviews-cancel-btn">Отмена</button>
                        <button type="submit" class="btn primary-btn review-form-submit">Отправить отзыв</button>
                    </div>
                </form>

                <div class="review-success" id="review-success" hidden>
                    <div class="review-success-icon"><i class="fa-solid fa-circle-check"></i></div>
                    <h3 class="review-success-title">Спасибо за ваш отзыв!</h3>
                    <p class="review-success-text">Ваш отзыв принят и будет опубликован после проверки модератором.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Модалка полного текста отзыва --}}
    <div class="review-modal-overlay" id="review-modal-overlay" hidden>
        <div class="review-modal" role="dialog" aria-modal="true" aria-labelledby="review-modal-name">
            <button class="review-modal-close" id="review-modal-close" aria-label="Закрыть">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="review-modal-header">
                <div class="review-modal-avatar" id="review-modal-avatar"></div>
                <div class="review-modal-info">
                    <span class="review-modal-name" id="review-modal-name"></span>
                    <span class="review-modal-date" id="review-modal-date"></span>
                </div>
                <div class="review-modal-stars" id="review-modal-stars"></div>
            </div>
            <p class="review-modal-text" id="review-modal-text"></p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('scripts/script-doctor-page.js') }}"></script>
@endpush
