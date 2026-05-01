@extends('layouts.error')

@section('title', '429 — Слишком много запросов | Маяк Здоровья')
@section('meta_description', 'Слишком много запросов с вашего устройства. Подождите немного и попробуйте снова.')

@section('content')
    <main class="error-page">
        @include('errors.partials.error-card', [
            'code' => 429,
            'heading' => 'Слишком много запросов',
            'copy' => 'Подождите немного и обновите страницу.',
        ])
    </main>
@endsection
