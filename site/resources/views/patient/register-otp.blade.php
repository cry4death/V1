@extends('layouts.app')

@section('title', 'Регистрация — код')
@section('body_class', 'patient-auth-page')

@section('content')
    <div class="container" style="max-width: 560px; padding: 3rem 1rem;">
        <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Регистрация</h1>
        <p style="color: #64748b; margin-bottom: 1.5rem;">Шаг 3 из 3: код из SMS</p>

        @if (session('status'))
            <p style="color: #15803d; margin-bottom: 1rem;">{{ session('status') }}</p>
        @endif

        <form method="post" action="{{ route('patient.register.complete') }}">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label for="otp">Код (6 цифр)</label>
                <input type="text" id="otp" name="otp" value="{{ old('otp') }}" required maxlength="6" inputmode="numeric" pattern="[0-9]*"
                       class="patient-input" style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; letter-spacing: 0.2em;">
                @error('otp') <span style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="btn primary-btn">Завершить регистрацию</button>
        </form>
        <p style="margin-top: 1rem;">
            <a href="{{ route('patient.register.phone') }}">Изменить номер</a>
        </p>
    </div>
@endsection
