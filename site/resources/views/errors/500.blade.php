@extends('layouts.error')

@section('title', '500 — Ошибка сервера | Маяк Здоровья')
@section('meta_description', 'На сервере произошла ошибка. Попробуйте позже или вернитесь на главную страницу «Маяк Здоровья».')

@section('content')
    <main class="error-page">
        @include('errors.partials.error-card', [
            'code' => 500,
            'heading' => 'Что-то пошло не так',
            'copy' => 'Мы уже разбираемся. Попробуйте обновить страницу позже или вернитесь на главную.',
        ])
    </main>
@endsection
