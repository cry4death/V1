@extends('layouts.app')

@section('title', 'Выбор врача')
@section('body_class', 'patient-booking-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/booking-wizard.css') }}?v=6">
@endpush

@section('content')
    <div class="cabinet-layout">
        @include('partials.cabinet-nav', ['navActive' => 'booking'])
        <main class="cabinet-main">
        <div class="booking-wizard" style="padding: 0;">
        @include('booking.partials.progress', [
            'step' => 2,
            'stepUrls' => [
                1 => route('booking.pickService', ['from' => 'any']),
            ],
        ])

        <h1>Выберите врача</h1>
        <p class="booking-lead">Услуга: <strong>{{ $service->name }}</strong></p>

        @if (session('error'))
            <div class="booking-alert booking-alert--error" role="alert">{{ session('error') }}</div>
        @endif

        @if ($doctors->isEmpty())
            <div class="booking-empty">
                К сожалению, у этой услуги нет привязанных врачей с доступной онлайн-записью. Оставьте заявку, и мы свяжемся с вами — или позвоните в клинику.
            </div>
        @else
            <div class="booking-doctors-grid">
                @foreach ($doctors as $doctor)
                    @include('partials.doctor-card', [
                        'doctor' => $doctor,
                        'doctorBookingUrl' => route('booking.pickSlot', ['service' => $service->slug, 'doctor' => $doctor->slug]),
                    ])
                @endforeach
            </div>
        @endif

        <div class="cabinet-actions" style="margin-top: 1.5rem;">
            <a href="{{ route('booking.pickService', $pinnedDoctor ? ['from' => 'doctor:'.$pinnedDoctor->slug] : ['from' => 'any']) }}" class="cabinet-btn cabinet-btn--ghost">← К услугам</a>
        </div>
        </div>
        </main>
    </div>
@endsection
