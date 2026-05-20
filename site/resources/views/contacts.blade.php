@extends('layouts.app')

@section('title', 'Контакты — Маяк Здоровья')
@section('meta_description', 'Контакты медицинской клиники в Минске. Адрес, телефон, email, карта проезда, форма обратной связи.')
@section('body_class', 'contacts-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/style-contacts-page.css') }}">
@endpush

@section('content')
    <div class="container">
        <nav class="breadcrumb" aria-label="Хлебные крошки">
            <a href="{{ route('home') }}" class="breadcrumb-link">Главная</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current">Контакты</span>
        </nav>
    </div>

    <main class="contacts-page-main">
        <div class="contacts-page-sea" aria-hidden="true">
            <i class="fa-solid fa-anchor contact-main-sea--anchor-lg"></i>
            <i class="fa-solid fa-binoculars contact-main-sea--binoculars"></i>
            <i class="fa-solid fa-life-ring contact-main-sea--lifebuoy-top"></i>
            <i class="fa-solid fa-compass contact-main-sea--compass"></i>
            <i class="fa-solid fa-water contact-main-sea--wave-top"></i>
            <i class="fa-solid fa-fish contact-main-sea--fish"></i>
            <i class="fa-solid fa-life-ring contact-main-sea--lifebuoy"></i>
            <i class="fa-solid fa-tower-observation contact-main-sea--lighthouse"></i>
            <i class="fa-solid fa-water contact-main-sea--wave-mid"></i>
            <i class="fa-solid fa-binoculars contact-main-sea--binoculars-low"></i>
            <i class="fa-solid fa-anchor contact-main-sea--anchor-sm"></i>
            <i class="fa-solid fa-ship contact-main-sea--ship-low"></i>
            <i class="fa-solid fa-compass contact-main-sea--compass-low"></i>
        </div>

        <section class="contact-hero">
            <div class="contact-hero-decor" aria-hidden="true">
                <i class="fa-solid fa-anchor contact-sea--anchor-lg"></i>
                <i class="fa-solid fa-tower-observation contact-sea--lighthouse"></i>
                <i class="fa-solid fa-ship contact-sea--ship"></i>
                <i class="fa-solid fa-compass contact-sea--compass"></i>
                <i class="fa-solid fa-water contact-sea--wave"></i>
                <i class="fa-solid fa-anchor contact-sea--anchor-sm"></i>
                <i class="fa-solid fa-fish contact-sea--fish"></i>
            </div>
            <div class="container">
                <div class="contact-hero-inner">
                    <div class="contact-hero-content">
                            <span class="contact-hero-badge"><i class="fa-solid fa-headset"></i> Контактный центр</span>
                        <h1>Свяжитесь с нами</h1>
                        <p>Ответим на вопросы, поможем с записью и подскажем удобное время приёма.</p>
                        <div class="contact-hero-meta">
                            <span><i class="fa-solid fa-phone-volume"></i> {{ $contacts['phone_short'] ?? '7289' }} ({{ $contacts['phone_short_note'] ?? 'A1, МТС, Life' }})</span>
                            <span><i class="fa-solid fa-clock"></i> Ежедневно, без выходных</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="contact-info-section">
            <div class="container">
                <div class="contact-cards contact-cards--three">
                    <div class="contact-card">
                        <div class="contact-card-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <h3>Наш адрес</h3>
                        <p>{{ $contacts['address'] ?? 'г. Минск, ул. К. Туровского, 14' }}</p>
                        @if (! empty($contacts['postal_address']))
                            <p class="contact-card-note">Почтовый: {{ $contacts['postal_address'] }}</p>
                        @endif
                        <a href="https://yandex.ru/maps/?text={{ urlencode($contacts['address'] ?? 'Минск') }}" target="_blank" class="contact-link">Проложить маршрут</a>
                    </div>

                    <div class="contact-card">
                        <div class="contact-card-icon"><i class="fa-solid fa-phone-volume"></i></div>
                        <h3>Связаться с нами</h3>
                        @php
                            $phoneShort = $contacts['phone_short'] ?? '7289';
                            $phoneShortNote = $contacts['phone_short_note'] ?? 'A1, МТС, Life';
                            $phoneShortTel = preg_replace('/[^\d+]/', '', $phoneShort);
                        @endphp
                        <p class="contact-phone-main"><a href="tel:{{ $phoneShortTel }}">{{ $phoneShort }}</a> <span class="contact-phone-note">({{ $phoneShortNote }})</span></p>
                        @if (! empty($contacts['phone_main']))
                            <p><a href="tel:{{ preg_replace('/[^\d+]/', '', $contacts['phone_main']) }}">{{ $contacts['phone_main'] }}</a></p>
                        @endif
                        @if (! empty($contacts['phones']) && is_array($contacts['phones']))
                            @foreach ($contacts['phones'] as $phone)
                                <p><a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">{{ $phone }}</a></p>
                            @endforeach
                        @endif
                        @if (! empty($contacts['phone_mobile']))
                            <p><a href="tel:{{ preg_replace('/[^\d+]/', '', $contacts['phone_mobile']) }}">{{ $contacts['phone_mobile'] }}</a></p>
                        @endif
                        @if (! empty($contacts['email']))
                            <div class="contact-card-divider"></div>
                            <p><a href="mailto:{{ $contacts['email'] }}"><i class="fa-solid fa-envelope"></i> {{ $contacts['email'] }}</a></p>
                        @endif
                    </div>

                    <div class="contact-card">
                        <div class="contact-card-icon"><i class="fa-solid fa-clock"></i></div>
                        <h3>Время работы</h3>
                        <p>ПН-ПТ: {{ $schedule['weekdays'] ?? '08:00 – 20:30' }}</p>
                        <p>СБ: {{ $schedule['saturday'] ?? '08:00 – 18:00' }}</p>
                        <p>ВС: {{ $schedule['sunday'] ?? '09:00 – 16:00' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="map-form-section">
            <div class="container">
                <div class="map-form-container">
                    <div class="map-container">
                        <h2>Как нас найти</h2>
                        <div class="map-frame">
                            <div id="contact-map"></div>
                        </div>
                    </div>

                    <div class="contact-form-container" id="contact-form">
                        <h2>Обратная связь</h2>
                        <p class="form-subtitle">Остались вопросы? Напишите нам!</p>
                        <form class="contact-form" method="POST" action="{{ route('contacts.store') }}">
                            @csrf
                            <div class="form-group">
                                <input type="text" id="name" name="name" placeholder="Ваше имя" required>
                                <label for="name">Имя</label>
                            </div>
                            <div class="form-group">
                                <input type="email" id="email" name="email" placeholder="email@example.by" required>
                                <label for="email">Email</label>
                            </div>
                            <div class="form-group">
                                <input type="tel" id="phone" name="phone" placeholder="+375 (__) ___-__-__" required>
                                <label for="phone">Телефон</label>
                            </div>
                            <div class="form-group">
                                <textarea id="message" name="message" rows="4" placeholder="Ваше сообщение..." required></textarea>
                                <label for="message">Сообщение</label>
                            </div>
                            <div class="form-privacy">
                                <input type="checkbox" id="privacy" name="privacy" required>
                                <label for="privacy">Я согласен на обработку <a href="#">персональных данных</a></label>
                            </div>
                            <button type="submit" class="btn primary-btn submit-btn">
                                <i class="fa-solid fa-paper-plane"></i> Отправить
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <section class="social-section">
            <div class="container">
                <h2>Присоединяйтесь к нам в социальных сетях</h2>
                <div class="social-icons">
                    @if (! empty($social['instagram']))
                        <a href="{{ $social['instagram'] }}" class="social-icon" aria-label="Instagram" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-instagram"></i></a>
                    @endif
                    @if (! empty($social['facebook']))
                        <a href="{{ $social['facebook'] }}" class="social-icon" aria-label="Facebook" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook-f"></i></a>
                    @endif
                    @if (! empty($social['vk']))
                        <a href="{{ $social['vk'] }}" class="social-icon" aria-label="VK" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-vk"></i></a>
                    @endif
                    @if (! empty($social['youtube']))
                        <a href="{{ $social['youtube'] }}" class="social-icon" aria-label="YouTube" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-youtube"></i></a>
                    @endif
                </div>
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    <script src="{{ asset('scripts/script-contacts-page.js') }}"></script>
@endpush
