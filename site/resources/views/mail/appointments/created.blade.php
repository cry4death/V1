@component('mail::message')
# Запись подтверждена

Здравствуйте, {{ $patientName }}!

**Дата и время:** {{ $appointment->start_at?->timezone(config('app.timezone'))->translatedFormat('d F Y, H:i') }}  
**Врач:** {{ $appointment->doctor ? $appointment->doctor->full_name : '—' }}  
**Услуга:** {{ $appointment->service?->name ?? '—' }}

@component('mail::button', ['url' => $cancelUrl])
Отменить запись
@endcomponent

Если вы передумали приходить, нажмите кнопку до наступления срока отмены, указанного в правилах клиники.

С уважением,<br>
{{ config('app.name') }}
@endcomponent
