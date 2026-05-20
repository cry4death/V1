@extends('layouts.app')

@section('title', 'Мои записи')
@section('body_class', 'patient-booking-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/booking-wizard.css') }}?v=6">
@endpush

@section('content')
    <div class="cabinet-layout">
        @include('partials.cabinet-nav', ['navActive' => 'appointments'])
        <main class="cabinet-main">
            <h1>Мои записи</h1>

            @if (session('status'))
                <div class="booking-alert booking-alert--ok" role="status">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="booking-alert booking-alert--error" role="alert">{{ session('error') }}</div>
            @endif

            <div class="cabinet-tabs" role="tablist">
                <a href="{{ route('cabinet.appointments.index', ['tab' => 'upcoming']) }}"
                   class="{{ $tab === 'upcoming' ? 'is-active' : '' }}"
                   role="tab"
                   @if ($tab === 'upcoming') aria-current="page" @endif>Предстоящие</a>
                <a href="{{ route('cabinet.appointments.index', ['tab' => 'past']) }}"
                   class="{{ $tab === 'past' ? 'is-active' : '' }}"
                   role="tab"
                   @if ($tab === 'past') aria-current="page" @endif>Прошедшие</a>
            </div>

            @if ($appointments->isEmpty())
                <div class="booking-empty">
                    @if ($tab === 'upcoming')
                        Нет предстоящих записей.
                    @else
                        Пока нет завершённых или отменённых записей в этом списке.
                    @endif
                </div>
            @else
                <div>
                    @foreach ($appointments as $appt)
                        @php
                            $statusLabel = match ($appt->status) {
                                \App\Enums\AppointmentStatus::New => 'Новая',
                                \App\Enums\AppointmentStatus::Processing => 'В обработке',
                                \App\Enums\AppointmentStatus::Completed => 'Завершена',
                                \App\Enums\AppointmentStatus::Cancelled => 'Отменена',
                                \App\Enums\AppointmentStatus::Rescheduled => 'Перенесена',
                            };
                            $badgeClass = match ($appt->status) {
                                \App\Enums\AppointmentStatus::Completed => 'cabinet-badge cabinet-badge--ok',
                                \App\Enums\AppointmentStatus::Cancelled => 'cabinet-badge cabinet-badge--err',
                                \App\Enums\AppointmentStatus::Processing => 'cabinet-badge cabinet-badge--warn',
                                default => 'cabinet-badge',
                            };
                        @endphp
                        <a href="{{ route('cabinet.appointments.show', $appt) }}"
                           class="cabinet-appt-card"
                           aria-label="Запись от {{ $appt->start_at?->timezone(config('app.timezone'))->translatedFormat('d.m.Y H:i') }}">
                            <div class="cabinet-appt-card__body">
                                <p class="cabinet-appt-card__date">
                                    @if ($appt->start_at)
                                        {{ $appt->start_at->timezone(config('app.timezone'))->translatedFormat('d.m.Y H:i') }}
                                    @else
                                        №{{ $appt->id }}
                                    @endif
                                    <span class="{{ $badgeClass }}" style="margin-left: 0.5rem;">{{ $statusLabel }}</span>
                                </p>
                                @if ($appt->service)
                                    <p class="cabinet-appt-card__service">{{ $appt->service->name }}</p>
                                @endif
                                @if ($appt->doctor)
                                    <p class="cabinet-appt-card__doctor">{{ $appt->doctor->full_name }}</p>
                                @endif
                            </div>
                            <i class="fa-solid fa-chevron-right cabinet-appt-card__chev" aria-hidden="true"></i>
                        </a>
                    @endforeach
                </div>

                <div style="margin-top: 1.5rem;">
                    {{ $appointments->links() }}
                </div>
            @endif

            <div class="cabinet-actions" style="margin-top: 1.5rem;">
                <a href="{{ route('booking.start') }}" class="cabinet-btn cabinet-btn--primary">Новая запись</a>
            </div>
        </main>
    </div>
@endsection
