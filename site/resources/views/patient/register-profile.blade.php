@extends('layouts.app')

@section('title', 'Регистрация — личные данные')
@section('body_class', 'patient-auth-page')

@section('content')
    <div class="container" style="max-width: 560px; padding: 3rem 1rem;">
        <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Регистрация</h1>
        <p style="color: #64748b; margin-bottom: 1.5rem;">Шаг 1 из 3: личные данные</p>

        @if (session('error'))
            <p style="color: #b91c1c; margin-bottom: 1rem;">{{ session('error') }}</p>
        @endif

        <form method="post" action="{{ route('patient.register.profile.store') }}" class="patient-form">
            @csrf
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="last_name">Фамилия</label>
                <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $profile['last_name'] ?? '') }}" required
                       class="patient-input" style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px;">
                @error('last_name') <span style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="first_name">Имя</label>
                <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $profile['first_name'] ?? '') }}" required
                       class="patient-input" style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px;">
                @error('first_name') <span style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="middle_name">Отчество</label>
                <input type="text" id="middle_name" name="middle_name" value="{{ old('middle_name', $profile['middle_name'] ?? '') }}"
                       class="patient-input" style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px;">
                @error('middle_name') <span style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="birth_date">Дата рождения</label>
                <input type="text" id="birth_date" name="birth_date" placeholder="дд.мм.гггг"
                       value="{{ old('birth_date', $profile['birth_date'] ?? '') }}" required
                       class="patient-input" style="width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px;">
                @error('birth_date') <span style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</span> @enderror
            </div>
            <fieldset style="border: none; padding: 0; margin-bottom: 1.25rem;">
                <legend style="font-weight: 600; margin-bottom: 0.5rem;">Пол</legend>
                <label style="margin-right: 1rem;">
                    <input type="radio" name="gender" value="male" @checked(old('gender', $profile['gender'] ?? '') === 'male') required> Мужской
                </label>
                <label>
                    <input type="radio" name="gender" value="female" @checked(old('gender', $profile['gender'] ?? '') === 'female')> Женский
                </label>
                @error('gender') <div style="color:#b91c1c;font-size:0.875rem;">{{ $message }}</div> @enderror
            </fieldset>
            <button type="submit" class="btn primary-btn">Далее</button>
        </form>
        <p style="margin-top: 1.5rem; font-size: 0.9rem;">
            Уже есть аккаунт? <a href="{{ route('patient.login') }}">Войти</a>
        </p>
    </div>
@endsection
