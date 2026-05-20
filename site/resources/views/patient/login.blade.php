@extends('layouts.app')

@section('title', 'Вход пациента')
@section('body_class', 'patient-auth-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/booking-wizard.css') }}?v=6">
@endpush

@section('content')
    <div class="container" style="max-width: 560px; padding: 3rem 1rem;">
        <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Вход</h1>
        <p style="color: #64748b; margin-bottom: 1.5rem;">По номеру телефона и коду</p>

        @if (session('error'))
            <p style="color: var(--error-color, #dc3545); margin-bottom: 1rem;">{{ session('error') }}</p>
        @endif

        <form method="post" action="{{ route('patient.login.request-otp') }}" id="login-phone-form">
            @csrf
            <input type="hidden" id="phone" name="phone" value="{{ old('phone') }}" class="phone-input-hidden">
            <div style="margin-bottom: 1.25rem;">
                <label for="phone_digits" style="display:block; font-weight:600; margin-bottom:0.4rem;">Телефон</label>
                <div class="phone-input-group">
                    <span class="phone-input-prefix">+375</span>
                    <input type="text" id="phone_digits" inputmode="numeric" autocomplete="tel-national"
                           placeholder="(XX) XXX-XX-XX" maxlength="14"
                           class="phone-input-digits"
                           value="{{ old('phone') ? preg_replace('/^\+?375/', '', old('phone')) : '' }}">
                </div>
                <p style="color: #64748b; font-size: 0.875rem; margin: 0.35rem 0 0;">Введите свой номер телефона.</p>
                @error('phone') <span style="color:var(--error-color,#dc3545);font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <div style="display: flex; gap: 0.75rem; margin-top: 0.25rem;">
                <a href="{{ route('patient.register.profile') }}" class="cabinet-btn cabinet-btn--ghost" style="flex: 1; justify-content: center;">Нет аккаунта? Регистрация</a>
                <button type="submit" class="cabinet-btn cabinet-btn--primary" style="flex: 1;">Получить код</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var digitsInput = document.getElementById('phone_digits');
    var hiddenInput = document.getElementById('phone');
    var form = document.getElementById('login-phone-form');

    function formatDigits(digits) {
        var out = '';
        for (var i = 0; i < digits.length; i++) {
            if (i === 0) { out += '('; }
            out += digits[i];
            if (i === 1) { out += ') '; }
            if (i === 4) { out += '-'; }
            if (i === 6) { out += '-'; }
        }
        return out;
    }

    function sync(digits) {
        digitsInput.value = formatDigits(digits);
        hiddenInput.value = digits.length === 9 ? ('+375' + digits) : '';
    }

    /* Backspace/Delete: operate on raw digits, skip formatting chars */
    digitsInput.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' || e.key === 'Delete') {
            e.preventDefault();
            var digits = this.value.replace(/\D/g, '').slice(0, 9);
            if (digits.length > 0) {
                digits = e.key === 'Delete' ? digits.slice(1) : digits.slice(0, -1);
            }
            sync(digits);
        }
    });

    /* Typing / paste */
    digitsInput.addEventListener('input', function () {
        var digits = this.value.replace(/\D/g, '').slice(0, 9);
        sync(digits);
    });

    form.addEventListener('submit', function () {
        var digits = digitsInput.value.replace(/\D/g, '');
        hiddenInput.value = '+375' + digits;
    });
}());
</script>
@endpush
