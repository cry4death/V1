@extends('layouts.error')

@section('title', '404 — Страница не найдена | Маяк Здоровья')
@section('meta_description', 'Страница не найдена. Вернитесь на главную сайта медицинского центра «Маяк Здоровья».')

@section('content')
    <main class="error-page">
        @include('errors.partials.error-card', [
            'code' => 404,
            'heading' => 'Что-то пошло не так',
            'copy' => 'Следуйте за маяком, вернитесь на главную',
        ])
    </main>
@endsection
