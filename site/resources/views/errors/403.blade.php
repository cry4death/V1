@extends('layouts.error')

@section('title', '403 — Доступ запрещён | Маяк Здоровья')
@section('meta_description', 'Доступ к этой странице ограничен. Вернитесь на главную сайта «Маяк Здоровья».')

@section('content')
    <main class="error-page">
        @include('errors.partials.error-card', [
            'code' => 403,
            'heading' => 'Доступ запрещён',
            'copy' => 'У вас нет прав для просмотра этой страницы. Вернитесь на главную.',
        ])
    </main>
@endsection
