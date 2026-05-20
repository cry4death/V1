@component('mail::message')
# Запись перенесена

Здравствуйте, {{ $patientName }}!

Новое время: **{{ $appointment->start_at?->timezone(config('app.timezone'))->translatedFormat('d F Y, H:i') }}**  
**Врач:** {{ $appointment->doctor ? $appointment->doctor->full_name : '—' }}  
**Услуга:** {{ $appointment->service?->name ?? '—' }}

@component('mail::button', ['url' => $cancelUrl])
Отменить запись
@endcomponent

{{ config('app.name') }}
@endcomponent
