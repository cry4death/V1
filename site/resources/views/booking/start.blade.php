@extends('layouts.app')

@section('title', 'Запись на приём')
@section('body_class', 'patient-booking-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/booking-wizard.css') }}?v=6">
@endpush

@section('content')
    <div class="cabinet-layout">
        @include('partials.cabinet-nav', ['navActive' => 'booking'])
        <main class="cabinet-main">
            <div class="booking-wizard" style="padding: 0;">
                <h1>Запись на приём</h1>
                <p class="booking-lead">
                    Выберите удобный сценарий: сначала услугу или сначала врача.
                </p>

                @if (session('status'))
                    <div class="booking-alert booking-alert--ok" role="status">{{ session('status') }}</div>
                @endif

                @if (session('error'))
                    <div class="booking-alert booking-alert--error" role="alert">{{ session('error') }}</div>
                @endif

                <div class="cabinet-actions" style="margin-top: 1.5rem;">
                    <a href="{{ route('booking.pickService', ['from' => 'any']) }}" class="cabinet-btn cabinet-btn--primary">
                        Выбрать услугу
                    </a>
                    <a href="{{ route('booking.browseDoctors') }}" class="cabinet-btn cabinet-btn--ghost">
                        Выбрать врача
                    </a>
                </div>
            </div>
        </main>
    </div>
@endsection
