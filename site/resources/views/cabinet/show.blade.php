@extends('layouts.app')

@section('title', 'Запись №'.$appointment->id)
@section('body_class', 'patient-booking-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/booking-wizard.css') }}?v=7">
@endpush

@section('content')
    <div class="cabinet-layout">
        @include('partials.cabinet-nav', ['navActive' => 'appointments'])
        <main class="cabinet-main">

            @if (session('status'))
                <div class="booking-alert booking-alert--ok" role="status">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="booking-alert booking-alert--error" role="alert">{{ session('error') }}</div>
            @endif

            @php
                $statusLabel = match ($appointment->status) {
                    \App\Enums\AppointmentStatus::New         => 'Новая',
                    \App\Enums\AppointmentStatus::Processing  => 'В обработке',
                    \App\Enums\AppointmentStatus::Completed   => 'Завершена',
                    \App\Enums\AppointmentStatus::Cancelled   => 'Отменена',
                    \App\Enums\AppointmentStatus::Rescheduled => 'Перенесена',
                };
                $badgeClass = match ($appointment->status) {
                    \App\Enums\AppointmentStatus::Completed   => 'cabinet-badge cabinet-badge--ok',
                    \App\Enums\AppointmentStatus::Cancelled   => 'cabinet-badge cabinet-badge--err',
                    \App\Enums\AppointmentStatus::Processing  => 'cabinet-badge cabinet-badge--warn',
                    \App\Enums\AppointmentStatus::Rescheduled => 'cabinet-badge cabinet-badge--warn',
                    default                                   => 'cabinet-badge cabinet-badge--info',
                };
            @endphp

            <div class="appt-show-title-row">
                <h1 class="appt-show-title">Запись №{{ $appointment->id }}</h1>
                <span class="{{ $badgeClass }}">{{ $statusLabel }}</span>
            </div>

            @if (in_array((string) ($appointment->espo_sync_status ?? ''), ['pending', 'failed'], true))
                <div class="booking-alert {{ $appointment->espo_sync_status === 'failed' ? 'booking-alert--error' : 'booking-alert--info' }}" role="alert" style="margin-bottom: 1.25rem;">
                    @if ($appointment->espo_sync_status === 'failed')
                        Не удалось синхронизировать с клиникой. Локальная запись актуальна — при необходимости позвоните нам.
                    @else
                        Синхронизация с клиникой выполняется. Данные уже сохранены.
                    @endif
                </div>
            @endif

            <div class="appt-show-card">
                @if ($appointment->start_at)
                    <div class="appt-show-datetime">
                        <div class="appt-show-datetime-label">Дата и время приёма</div>
                        <div class="appt-show-datetime-date">
                            {{ $appointment->start_at->timezone(config('app.timezone'))->translatedFormat('d F Y') }}
                        </div>
                        <div class="appt-show-datetime-time">
                            {{ $appointment->start_at->timezone(config('app.timezone'))->format('H:i') }}
                        </div>
                    </div>
                    <div class="appt-show-divider"></div>
                @endif

                <dl class="appt-show-dl">
                    @if ($appointment->service)
                        <div class="appt-show-dl-row">
                            <dt>Услуга</dt>
                            <dd>{{ $appointment->service->name }}</dd>
                        </div>
                    @endif
                    @if ($appointment->doctor)
                        <div class="appt-show-dl-row">
                            <dt>Врач</dt>
                            <dd>{{ $appointment->doctor->full_name }}</dd>
                        </div>
                    @endif
                    @if ($appointment->message)
                        <div class="appt-show-dl-row">
                            <dt>Комментарий</dt>
                            <dd style="white-space: pre-wrap;">{{ $appointment->message }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="appt-show-actions">
                @if ($canReschedule)
                    <a href="{{ route('cabinet.appointments.reschedule', $appointment) }}" class="cabinet-btn cabinet-btn--primary appt-show-btn">Перенести</a>
                @endif
                @if ($canCancel)
                    <form method="post" action="{{ route('cabinet.appointments.cancel', $appointment) }}" class="appt-show-btn">
                        @csrf
                        <input type="hidden" name="reason" value="">
                        <button type="submit" class="cabinet-btn cabinet-btn--danger" style="width:100%;">Отменить запись</button>
                    </form>
                @endif
                <a href="{{ route('booking.start') }}" class="cabinet-btn cabinet-btn--primary appt-show-btn">Новая запись</a>
            </div>

        </main>
    </div>
@endsection
