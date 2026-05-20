<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Медицинская клиника «Маяк Здоровья» — профессиональные медицинские услуги, консультации врачей, диагностика и лечение. Запишитесь на приём сегодня!')">
    <title>@yield('title', 'Маяк Здоровья — медицинская клиника')</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('styles/styles.css') }}?v=12">
    <link rel="stylesheet" href="{{ asset('styles/style-medical-services-page.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('styles/style-our-doctors-page.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('styles/promotions-slider.css') }}?v=6">
    @stack('styles')
</head>
<body class="@yield('body_class')" data-booking-url="{{ route('booking.start') }}">
    @include('partials.header')

    @yield('content')

    @include('partials.footer')

    <div id="imageLightbox" class="image-lightbox" aria-hidden="true">
        <span class="image-lightbox-close" role="button" aria-label="Закрыть">
            <i class="fa-solid fa-xmark"></i>
        </span>
        <img id="imageLightboxImg" src="" alt="">
    </div>

    <div id="licenseModal" class="license-modal">
        <div class="license-modal-content">
            <button class="license-zoom-btn" aria-label="Увеличить изображение">
                <i class="fa-solid fa-magnifying-glass-plus"></i>
            </button>
            <span class="license-modal-close" aria-label="Закрыть просмотр" role="button">
                <i class="fa-solid fa-xmark"></i>
            </span>
            <img id="modalLicenseImage" src="" alt="Увеличенное изображение">
        </div>
    </div>

    <script>window.BOOKING_INDEX_URL = @json(route('booking.start'));</script>
    <script src="{{ asset('scripts/shared-utils.js') }}?v=7"></script>
    <script src="{{ asset('scripts/script.js') }}?v=14"></script>
    @stack('scripts')
</body>
</html>
