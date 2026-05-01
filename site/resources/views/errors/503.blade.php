@extends('layouts.error')

@section('title', '503 — Сервис недоступен | Маяк Здоровья')
@section('meta_description', 'Сайт временно недоступен. Загляните чуть позже или вернитесь на главную «Маяк Здоровья».')

@section('content')
    <main class="error-page">
        @include('errors.partials.error-card', [
            'code' => 503,
            'heading' => 'Сайт временно недоступен',
            'copy' => 'Ведутся технические работы или высокая нагрузка. Пожалуйста, зайдите позже.',
        ])
    </main>
@endsection
