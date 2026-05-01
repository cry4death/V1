@extends('layouts.app')

@section('title', 'Запись на приём')
@section('body_class', 'patient-booking-page')

@section('content')
    <div class="container" style="max-width: 720px; padding: 3rem 1rem;">
        <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem;">Онлайн-запись</h1>
        <p style="color: #64748b; margin-bottom: 1.5rem;">
            Выберите услугу и врача. Время приёма уточним отдельно.
        </p>

        @if (session('status'))
            <p style="color: #15803d; margin-bottom: 1.25rem; padding: 0.75rem; background: #f0fdf4; border-radius: 8px;">{{ session('status') }}</p>
        @endif

        <form method="get" action="{{ route('booking.index') }}" style="margin-bottom: 2rem;">
            <label for="service_slug" style="display:block;font-weight:600;margin-bottom:0.5rem;">Услуга</label>
            <select id="service_slug" name="service" onchange="this.form.submit()"
                    style="width:100%; max-width:100%; padding:0.6rem; border-radius:8px; border:1px solid #cbd5e1;">
                <option value="">— Выберите услугу —</option>
                @foreach ($services as $s)
                    <option value="{{ $s->slug }}" @selected($selectedService && $selectedService->id === $s->id)>
                        {{ $s->direction ? $s->direction->name.' — ' : '' }}{{ $s->name }}
                    </option>
                @endforeach
            </select>
        </form>

        @if ($selectedService)
            <form method="post" action="{{ route('booking.store') }}">
                @csrf
                <input type="hidden" name="service_id" value="{{ $selectedService->id }}">

                <label for="doctor_id" style="display:block;font-weight:600;margin-bottom:0.5rem;">Врач</label>
                @if ($doctors->isEmpty())
                    <p style="color:#b91c1c;">К этой услуге пока не привязаны врачи в системе.</p>
                @else
                    <select id="doctor_id" name="doctor_id" required
                            style="width:100%; padding:0.6rem; border-radius:8px; border:1px solid #cbd5e1; margin-bottom:1rem;">
                        <option value="">— Выберите врача —</option>
                        @foreach ($doctors as $d)
                            <option value="{{ $d->id }}" @selected(old('doctor_id') == $d->id)>
                                {{ $d->last_name }} {{ $d->first_name }}
                                @if ($d->specialization)
                                    — {{ $d->specialization->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('doctor_id') <p style="color:#b91c1c;">{{ $message }}</p> @enderror
                    @error('service_id') <p style="color:#b91c1c;">{{ $message }}</p> @enderror

                    <button type="submit" class="btn primary-btn">Отправить заявку</button>
                @endif
            </form>
        @else
            <p style="color:#64748b;">Сначала выберите услугу в списке выше.</p>
        @endif
    </div>
@endsection
