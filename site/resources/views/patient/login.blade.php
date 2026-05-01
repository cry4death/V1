@extends('layouts.app')

@section('title', 'Вход пациента')
@section('body_class', 'patient-auth-page')

@section('content')
    <div class="container" style="max-width: 560px; padding: 3rem 1rem;">
        <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Вход</h1>
        <p style="color: #64748b; margin-bottom: 1.5rem;">По номеру телефона и коду</p>

        @if (session('error'))
            <p style="color: #b91c1c; margin-bottom: 1rem;">{{ session('error') }}</p>
        @endif

        <form method="post" action="{{ route('patient.login.request-otp') }}">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label for="phone">Телефон</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" required
                       class="patient-input" style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px;">
                @error('phone') <span style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="btn primary-btn">Получить код</button>
        </form>
        <p style="margin-top: 1.5rem;">
            Нет аккаунта? <a href="{{ route('patient.register.profile') }}">Регистрация</a>
        </p>
    </div>
@endsection
