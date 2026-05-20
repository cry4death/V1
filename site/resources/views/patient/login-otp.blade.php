@extends('layouts.app')

@section('title', 'Вход — код')
@section('body_class', 'patient-auth-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/booking-wizard.css') }}?v=6">
@endpush

@section('content')
    <div class="container" style="max-width: 560px; padding: 3rem 1rem;">
        <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Вход</h1>
        <p style="color: #64748b; margin-bottom: 1.5rem;">Введите код из SMS</p>

        @if (session('status'))
            <p style="color: var(--secondary-color, #1e5a99); margin-bottom: 1rem;">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div class="booking-alert booking-alert--error" role="alert" style="margin-bottom: 1rem;">
                <ul style="margin: 0; padding-left: 1.1rem;">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ route('patient.login.verify') }}">
            @csrf
            <div style="margin-bottom: 1.25rem;">
                <label for="otp" style="display:block; font-weight:600; margin-bottom:0.4rem;">Код (6 цифр)</label>
                <input type="text" id="otp" name="otp" value="{{ old('otp') }}" required maxlength="6"
                       inputmode="numeric" pattern="[0-9]*"
                       class="cabinet-input" style="letter-spacing: 0.25em; font-size: 1.1rem;">
                @error('otp') <span style="color:var(--error-color,#dc3545);font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <a href="{{ route('patient.login') }}" class="cabinet-btn cabinet-btn--ghost" style="flex: 1; justify-content: center;">Другой номер</a>
                <button type="submit" class="cabinet-btn cabinet-btn--primary" style="flex: 1;">Войти</button>
            </div>
        </form>
    </div>
@endsection
