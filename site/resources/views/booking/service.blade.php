@extends('layouts.app')

@section('title', 'Выбор услуги')
@section('body_class', 'patient-booking-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('styles/booking-wizard.css') }}?v=6">
    <link rel="stylesheet" href="{{ asset('styles/style-medical-services-page.css') }}">
@endpush

@section('content')
    <div class="cabinet-layout">
        @include('partials.cabinet-nav', ['navActive' => 'booking'])
        <main class="cabinet-main">
        <div class="booking-wizard" style="padding: 0;">
        @include('booking.partials.progress', ['step' => 1])

        <h1>Выберите услугу</h1>
        @if ($pinnedDoctor)
            <p class="booking-lead">
                Запись к врачу: <strong>{{ $pinnedDoctor->full_name }}</strong>
            </p>
        @else
            <p class="booking-lead">Укажите направление или услугу из списка.</p>
        @endif

        @if (session('error'))
            <div class="booking-alert booking-alert--error" role="alert">{{ session('error') }}</div>
        @endif

        @if ($directionBlocks->isEmpty() && $servicesWithoutDirection->isEmpty())
            <div class="booking-empty">
                @if ($pinnedDoctor)
                    К сожалению, у этого врача нет услуг с онлайн-записью. Позвоните в клинику — раздел «Контакты».
                @else
                    Нет доступных услуг для записи. Попробуйте позже или свяжитесь с нами.
                @endif
            </div>
        @else
            <div class="booking-accordion">
                @foreach ($directionBlocks as $block)
                    <div class="booking-acc-item">
                        <button type="button" class="booking-acc-header" aria-expanded="false">
                            <span>{{ $block['direction']->name }}</span>
                            <i class="fa-solid fa-chevron-down booking-acc-icon"></i>
                        </button>
                        <div class="booking-acc-body">
                            <div class="category-services-list">
                                @foreach ($block['services'] as $s)
                                    <a class="category-service-row" href="{{ route('booking.pickDoctor', ['service' => $s->slug]) }}">
                                        <span class="category-service-name">{{ $s->name }}</span>
                                        <span class="category-service-right">
                                            @if ($s->price > 0)
                                                <span class="category-service-price">От {{ number_format($s->price, 2, '.', ' ') }} BYN</span>
                                            @endif
                                            <span class="category-service-chevron"><i class="fa-solid fa-chevron-right"></i></span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                @if ($servicesWithoutDirection->isNotEmpty())
                    <div class="booking-acc-item">
                        <button type="button" class="booking-acc-header" aria-expanded="false">
                            <span>Прочее</span>
                            <i class="fa-solid fa-chevron-down booking-acc-icon"></i>
                        </button>
                        <div class="booking-acc-body">
                            <div class="category-services-list">
                                @foreach ($servicesWithoutDirection as $s)
                                    <a class="category-service-row" href="{{ route('booking.pickDoctor', ['service' => $s->slug]) }}">
                                        <span class="category-service-name">{{ $s->name }}</span>
                                        <span class="category-service-right">
                                            @if ($s->price > 0)
                                                <span class="category-service-price">От {{ number_format($s->price, 2, '.', ' ') }} BYN</span>
                                            @endif
                                            <span class="category-service-chevron"><i class="fa-solid fa-chevron-right"></i></span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <div class="cabinet-actions" style="margin-top: 1.5rem;">
            <a href="{{ route('booking.start') }}" class="cabinet-btn cabinet-btn--ghost">← Назад</a>
        </div>
        </div>
        </main>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.booking-acc-header').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var item = btn.closest('.booking-acc-item');
        var body = item.querySelector('.booking-acc-body');
        var isOpen = btn.getAttribute('aria-expanded') === 'true';

        if (isOpen) {
            btn.setAttribute('aria-expanded', 'false');
            body.style.maxHeight = body.scrollHeight + 'px';
            requestAnimationFrame(function () { body.style.maxHeight = '0'; });
        } else {
            btn.setAttribute('aria-expanded', 'true');
            body.style.maxHeight = body.scrollHeight + 'px';
            body.addEventListener('transitionend', function handler() {
                body.style.maxHeight = 'none';
                body.removeEventListener('transitionend', handler);
            });
        }
    });
});
</script>
@endpush
