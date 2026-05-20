@extends('layouts.app')

@section('title', 'Выбор врача')
@section('body_class', 'patient-booking-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/booking-wizard.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('styles/style-our-doctors-page.css') }}">
@endpush

@section('content')
    <div class="cabinet-layout">
        @include('partials.cabinet-nav', ['navActive' => 'booking'])
        <main class="cabinet-main">
        <div class="booking-wizard" style="padding: 0;">
        <h1>Сначала выберите врача</h1>
        <p class="booking-lead">Нажмите «Записаться» у нужного специалиста — далее вы увидите его услуги.</p>

        @if (session('error'))
            <div class="booking-alert booking-alert--error" role="alert">{{ session('error') }}</div>
        @endif

        @if ($doctors->isEmpty())
            <div class="booking-empty">
                К сожалению, сейчас нет врачей с открытой онлайн-записью. Оставьте заявку по телефону на странице «Контакты».
            </div>
        @else
            {{-- Обёртка имитирует структуру .doctors-section .container чтобы переиспользовать JS фильтров --}}
            <div class="doctors-section" style="padding: 0; background: none;">
            <div class="container" style="padding: 0; max-width: none;">

            <div class="doctors-filters" style="margin-bottom: 1.5rem;">
                <div class="doctors-search-wrap">
                    <i class="fas fa-search doctors-search-icon" aria-hidden="true"></i>
                    <input type="text" class="doctors-search-input" placeholder="Поиск по имени..." aria-label="Поиск по имени">
                </div>
                <div class="doctors-filters-right">
                    <div class="doctors-filter-group">
                        <div class="custom-select-wrap">
                            <select class="doctors-filter-select" id="filter-specialization" aria-label="Специализация">
                                <option value="">Все специализации</option>
                                @foreach ($specializations as $spec)
                                    <option value="{{ $spec->name }}">{{ $spec->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">Все специализации</button>
                            <div class="custom-select-dropdown" role="listbox" aria-hidden="true"></div>
                        </div>
                    </div>
                    <div class="doctors-filter-group">
                        <div class="custom-select-wrap">
                            <select class="doctors-filter-select" id="filter-category" aria-label="Категория">
                                <option value="">Все категории</option>
                                <option value="Высшая категория">Высшая категория</option>
                                <option value="Первая категория">Первая категория</option>
                                <option value="Вторая категория">Вторая категория</option>
                            </select>
                            <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">Все категории</button>
                            <div class="custom-select-dropdown" role="listbox" aria-hidden="true"></div>
                        </div>
                    </div>
                    <div class="doctors-filter-group">
                        <div class="custom-select-wrap">
                            <select class="doctors-filter-select" id="filter-age" aria-label="Возраст пациентов">
                                <option value="">Все возрасты</option>
                                <option value="adults">Взрослые</option>
                                <option value="children">Дети</option>
                            </select>
                            <button type="button" class="custom-select-trigger" aria-haspopup="listbox" aria-expanded="false">Все возрасты</button>
                            <div class="custom-select-dropdown" role="listbox" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>
                <button type="button" class="doctors-reset-btn" aria-label="Сбросить фильтры">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>

            <div class="doctors-container booking-doctors-grid">
                @foreach ($doctors as $doctor)
                    @include('partials.doctor-card', [
                        'doctor' => $doctor,
                        'doctorBookingUrl' => route('booking.pickService', ['from' => 'doctor:'.$doctor->slug]),
                    ])
                @endforeach
            </div>

            </div>{{-- /.container --}}
            </div>{{-- /.doctors-section --}}
        @endif

        <div class="cabinet-actions" style="margin-top: 1.5rem;">
            <a href="{{ route('booking.start') }}" class="cabinet-btn cabinet-btn--ghost">← Назад к выбору сценария</a>
        </div>
        </div>
        </main>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('scripts/script-our-doctors-page.js') }}"></script>
@endpush
