@extends('layouts.app')

@section('title', 'Отмена записи')
@section('body_class', 'patient-booking-page')

@section('content')
    <div class="container" style="max-width: 560px; padding: 3rem 1rem;">
        @isset($success)
            <h1 style="font-size: 1.5rem; color: #15803d;">Запись отменена</h1>
            <p style="color: #64748b; margin-top: 1rem;">Если передумаете — можно записаться снова на сайте.</p>
        @else
            <h1 style="font-size: 1.5rem; color: #b91c1c;">Не удалось отменить</h1>
            <p style="margin-top: 1rem;">{{ $error ?? 'Попробуйте позже или свяжитесь с клиникой.' }}</p>
        @endif
        <p style="margin-top: 2rem;">
            <a href="{{ route('home') }}" class="btn primary-btn">На главную</a>
        </p>
    </div>
@endsection
