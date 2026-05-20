@component('mail::message')
# Запись отменена

Здравствуйте, {{ $patientName }}!

Запись **{{ $appointment->start_at?->timezone(config('app.timezone'))->translatedFormat('d F Y, H:i') }}**  
({{ $appointment->service?->name ?? 'услуга' }}, {{ $appointment->doctor ? $appointment->doctor->full_name : 'врач' }}) отменена.

Если это ошибка, запишитесь снова на сайте или по телефону клиники.

{{ config('app.name') }}
@endcomponent
