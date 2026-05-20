<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Страница ошибки медицинского центра «Маяк Здоровья». Вернитесь на главную страницу сайта.')">
    <title>@yield('title', 'Ошибка — Маяк Здоровья')</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('styles/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('styles/style-error-page.css') }}">
</head>
<body class="error-page-view" data-booking-url="{{ route('booking.start') }}">
    @include('partials.header')

    @yield('content')

    @include('partials.footer')

    <script>window.BOOKING_INDEX_URL = @json(route('booking.start'));</script>
    <script src="{{ asset('scripts/shared-utils.js') }}?v=7"></script>
    <script src="{{ asset('scripts/script.js') }}?v=12"></script>
</body>
</html>
