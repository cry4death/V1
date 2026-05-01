@extends('layouts.error')

@section('title', '419 — Сессия истекла | Маяк Здоровья')
@section('meta_description', 'Срок действия страницы истёк. Обновите страницу и попробуйте снова.')

@section('content')
    <main class="error-page">
        @include('errors.partials.error-card', [
            'code' => 419,
            'heading' => 'Страница устарела',
            'copy' => 'Обновите страницу и отправьте форму ещё раз.',
        ])
    </main>
@endsection
